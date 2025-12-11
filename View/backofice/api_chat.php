<?php
// Lightweight proxy to OpenAI chat completion for the admin UI.
putenv("OPENAI_API_KEY=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");

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

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
// execute
$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($resp === false) {
    http_response_code(502);
    echo json_encode(['error' => 'Request to OpenAI failed', 'details' => $curlErr]);
    exit;
}

// If unauthorized (401) or other non-2xx, surface error with OpenAI response for debugging
if ($httpCode === 401) {
    http_response_code(401);
    $bodyDecoded = json_decode($resp, true);
    error_log('api_chat.php: OpenAI returned 401: ' . ($bodyDecoded ? json_encode($bodyDecoded) : $resp));
    echo json_encode([
        'error' => 'Unauthorized: OpenAI API key is invalid or not permitted (HTTP 401).',
        'openai_http_code' => $httpCode,
        'openai_body' => $bodyDecoded !== null ? $bodyDecoded : $resp
    ]);
    exit;
}

if ($httpCode < 200 || $httpCode >= 300) {
    http_response_code(502);
    $bodyDecoded = json_decode($resp, true);
    error_log('api_chat.php: OpenAI returned HTTP ' . $httpCode . ': ' . ($bodyDecoded ? json_encode($bodyDecoded) : $resp));
    echo json_encode([
        'error' => 'OpenAI returned unexpected HTTP status',
        'openai_http_code' => $httpCode,
        'openai_body' => $bodyDecoded !== null ? $bodyDecoded : $resp
    ]);
    exit;
}

// Try to decode and echo back the JSON reply from OpenAI
$decoded = json_decode($resp, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo json_encode($decoded);
} else {
    // If response is not JSON, return raw text
    echo json_encode(['raw' => $resp, 'http_code' => $httpCode]);
}
