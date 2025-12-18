<?php
header('Content-Type: application/json');

// Include config to access API key
require_once __DIR__ . '/../../config.php';

function respond($data) {
  echo json_encode($data);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  respond(['success' => false, 'message' => 'POST only']);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$title = trim($input['title'] ?? '');
$gameType = trim($input['gameType'] ?? '');
$format = trim($input['tournamentFormat'] ?? '');
$playerCount = trim($input['playerCount'] ?? '');
$theme = trim($input['theme'] ?? '');

if ($title === '' && $gameType === '' && $format === '' && $theme === '') {
  respond(['success' => false, 'message' => 'Please provide at least one detail (game type, format, theme).']);
}

function placeholderImage($label, $theme, $size = '1024x1024') {
  list($w, $h) = explode('x', $size);
  $bg1 = '#1aff87';
  $bg2 = '#0a0a0a';
  $text = htmlspecialchars($label . ' · ' . ($theme ?: 'GameBridge AI'), ENT_QUOTES, 'UTF-8');
  $svg = <<<SVG
<svg width="{$w}" height="{$h}" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$bg1}" stop-opacity="0.8"/>
      <stop offset="100%" stop-color="{$bg2}" stop-opacity="0.9"/>
    </linearGradient>
  </defs>
  <rect width="100%" height="100%" fill="url(#grad)"/>
  <text x="50%" y="45%" dominant-baseline="middle" text-anchor="middle" font-size="34" font-family="Poppins, Arial" fill="#0c0c0c" font-weight="700">{$text}</text>
  <text x="50%" y="58%" dominant-baseline="middle" text-anchor="middle" font-size="18" font-family="Poppins, Arial" fill="#e1ffe4">Placeholder preview</text>
</svg>
SVG;
  return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function callOpenAiChat($prompt, $apiKey) {
  $body = json_encode([
    'model' => 'gpt-4o-mini',
    'messages' => [
      ['role' => 'system', 'content' => 'You write punchy, hype gaming event descriptions.'],
      ['role' => 'user', 'content' => $prompt]
    ],
    'max_tokens' => 220,
    'temperature' => 0.8,
  ]);
  $ch = curl_init('https://api.openai.com/v1/chat/completions');
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
      'Content-Type: application/json',
      'Authorization: Bearer ' . $apiKey
    ],
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_SSL_VERIFYPEER => true
  ]);
  $resp = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $curlError = curl_error($ch);
  curl_close($ch);
  
  if ($resp === false || !empty($curlError)) {
    error_log("OpenAI Chat API Error: " . $curlError);
    return null;
  }
  
  if ($httpCode !== 200) {
    error_log("OpenAI Chat API HTTP Error: " . $httpCode . " - " . $resp);
    return null;
  }
  
  $decoded = json_decode($resp, true);
  if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("OpenAI Chat API JSON Error: " . json_last_error_msg());
    return null;
  }
  
  return $decoded['choices'][0]['message']['content'] ?? null;
}

function callOpenAiImage($prompt, $apiKey, $size = '1024x1024') {
  $body = json_encode([
    'model' => 'dall-e-3',
    'prompt' => $prompt,
    'n' => 1,
    'size' => $size,
    'response_format' => 'b64_json'
  ]);
  $ch = curl_init('https://api.openai.com/v1/images/generations');
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
      'Content-Type: application/json',
      'Authorization: Bearer ' . $apiKey
    ],
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true
  ]);
  $resp = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $curlError = curl_error($ch);
  curl_close($ch);
  
  if ($resp === false || !empty($curlError)) {
    error_log("OpenAI Image API Error: " . $curlError);
    return null;
  }
  
  if ($httpCode !== 200) {
    error_log("OpenAI Image API HTTP Error: " . $httpCode . " - " . $resp);
    return null;
  }
  
  $decoded = json_decode($resp, true);
  if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("OpenAI Image API JSON Error: " . json_last_error_msg());
    return null;
  }
  
  $b64 = $decoded['data'][0]['b64_json'] ?? null;
  if (!$b64) return null;
  return 'data:image/png;base64,' . $b64;
}

$apiKey = config::getOpenAiApiKey();
$usedAi = false;
$description = '';
$poster = $banner = $thumb = '';

$prompt = "Create a concise, hype description for a gaming event.\n".
          "Title: {$title}\nGame type: {$gameType}\nFormat: {$format}\nPlayers: {$playerCount}\nTheme: {$theme}\n".
          "Keep it under 110 words, include a hook + what to expect + call to action.";

if ($apiKey) {
  $desc = callOpenAiChat($prompt, $apiKey);
  if ($desc) {
    $description = trim($desc);
    $usedAi = true;
  }
  $imagePromptBase = "{$theme} {$gameType} tournament, {$format}, {$playerCount} players, vibrant poster style, high detail, trending on artstation";
  $poster = callOpenAiImage("Poster: " . $imagePromptBase, $apiKey) ?: '';
  $banner = callOpenAiImage("Wide banner, cinematic: " . $imagePromptBase, $apiKey) ?: '';
  $thumb = callOpenAiImage("Square thumbnail, bold logo, {$theme} {$gameType}", $apiKey) ?: '';
}

// Fallbacks when API unavailable or incomplete
if ($description === '') {
  $description = sprintf(
    "%s showdown incoming! %s format with %s players. Theme: %s. Claim your slot, stack your squad, and chase the crown.",
    $gameType ?: 'Gaming',
    $format ?: 'community bracket',
    $playerCount ?: 'top players',
    $theme ?: 'neon future'
  );
}

if (!$poster) $poster = placeholderImage('Poster', $theme);
if (!$banner) $banner = placeholderImage('Banner', $theme, '1200x400');
if (!$thumb) $thumb = placeholderImage('Thumbnail', $theme, '512x512');

$message = $usedAi ? 'AI content generated. You can edit before saving.' : 'Fallback templates used (set OPENAI_API_KEY to enable live AI).';

respond([
  'success' => true,
  'description' => $description,
  'poster' => $poster,
  'banner' => $banner,
  'thumbnail' => $thumb,
  'message' => $message
]);

