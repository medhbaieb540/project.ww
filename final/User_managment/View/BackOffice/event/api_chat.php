<?php
// Lightweight proxy to OpenAI chat completion for the admin UI.
// NOTE: Do NOT hardcode API keys in source. Configure via environment variable
// `OPENAI_API_KEY` or implement `config::getOpenAiApiKey()`.

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Read input
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$userMessage = trim($input['message'] ?? '');

// Resolve API key: prefer config helper if available, then environment
$apiKey = '';
if (class_exists('config') && method_exists('config', 'getOpenAiApiKey')) {
    try {
        $apiKey = config::getOpenAiApiKey();
    } catch (Throwable $e) {
        $apiKey = '';
    }
}
if (empty($apiKey)) {
    $apiKey = getenv('OPENAI_API_KEY') ?: '';
}

if (empty($userMessage)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing "message" in request body.']);
    exit;
}

if (empty($apiKey)) {
    http_response_code(401);
    echo json_encode(['error' => 'OpenAI API key not configured. Set OPENAI_API_KEY in environment or implement config::getOpenAiApiKey().']);
    exit;
}

$url = 'https://api.openai.com/v1/chat/completions';
$payload = [
    'model' => 'gpt-4o-mini',
    'messages' => [ [ 'role' => 'user', 'content' => $userMessage ] ],
    'temperature' => 0.7,
    'max_tokens' => 500
];

// Helper to perform a single request to OpenAI
function openai_request_once($url, $apiKey, $payload, &$curlErrOut = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);
    $curlErrOut = $curlErr;
    return ['http_code' => $httpCode, 'body' => $resp];
}

// Retry/backoff wrapper: for transient errors (5xx, 429 without explicit insufficient_quota)
$maxAttempts = 3;
$attempt = 0;
$lastResp = null;
$lastCurlErr = null;
while ($attempt < $maxAttempts) {
    $attempt++;
    $res = openai_request_once($url, $apiKey, $payload, $lastCurlErr);
    $lastResp = $res['body'];
    $httpCode = (int)$res['http_code'];

    // decode body to inspect error codes
    $bodyDecoded = json_decode($lastResp, true);

    // If successful, break
    if ($httpCode >= 200 && $httpCode < 300) {
        break;
    }

    // If quota error (insufficient_quota) — do not retry, surface actionable message
    if ($httpCode === 429 && isset($bodyDecoded['error']['code']) && $bodyDecoded['error']['code'] === 'insufficient_quota') {
        // surface immediately
        http_response_code(429);
        error_log('api_chat.php: OpenAI insufficient_quota: ' . json_encode($bodyDecoded));
        echo json_encode([
            'error' => 'OpenAI quota exceeded: please check your plan and billing details.',
            'openai_http_code' => $httpCode,
            'openai_body' => $bodyDecoded
        ]);
        exit;
    }

    // For other 4xx errors (like 401), do not retry
    if ($httpCode >= 400 && $httpCode < 500) {
        break;
    }

    // For 5xx or generic 429 (rate limit), retry with exponential backoff (unless last attempt)
    if ($attempt < $maxAttempts) {
        // backoff  (sleep seconds)
        sleep(pow(2, $attempt - 1));
        continue;
    }
    break;
}

// If no response captured
if ($lastResp === null) {
    http_response_code(502);
    echo json_encode(['error' => 'Request to OpenAI failed', 'details' => $lastCurlErr ?: 'no response']);
    exit;
}

// Handle common statuses
$bodyDecoded = json_decode($lastResp, true);
if ($httpCode === 401) {
    http_response_code(401);
    error_log('api_chat.php: OpenAI returned 401: ' . ($bodyDecoded ? json_encode($bodyDecoded) : $lastResp));
    echo json_encode([
        'error' => 'Unauthorized: OpenAI API key invalid or not permitted (HTTP 401).',
        'openai_http_code' => $httpCode,
        'openai_body' => $bodyDecoded !== null ? $bodyDecoded : $lastResp
    ]);
    exit;
}

if ($httpCode === 429) {
    // If we reach here, it's a 429 that was not explicitly insufficient_quota.
    http_response_code(429);
    error_log('api_chat.php: OpenAI returned 429: ' . ($bodyDecoded ? json_encode($bodyDecoded) : $lastResp));
    echo json_encode([
        'error' => 'OpenAI rate-limited the request. Try again later.',
        'openai_http_code' => $httpCode,
        'openai_body' => $bodyDecoded !== null ? $bodyDecoded : $lastResp
    ]);
    exit;
}

if ($httpCode < 200 || $httpCode >= 300) {
    http_response_code(502);
    error_log('api_chat.php: OpenAI returned HTTP ' . $httpCode . ': ' . ($bodyDecoded ? json_encode($bodyDecoded) : $lastResp));
    echo json_encode([
        'error' => 'OpenAI returned unexpected HTTP status',
        'openai_http_code' => $httpCode,
        'openai_body' => $bodyDecoded !== null ? $bodyDecoded : $lastResp
    ]);
    exit;
}

// Success — return parsed JSON (or raw if not JSON)
$decoded = json_decode($lastResp, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo json_encode($decoded);
} else {
    echo json_encode(['raw' => $lastResp, 'http_code' => $httpCode]);
}
