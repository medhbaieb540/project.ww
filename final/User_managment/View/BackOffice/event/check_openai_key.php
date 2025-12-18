<?php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');

$env = getenv('OPENAI_API_KEY');
$configVal = null;
try {
    if (class_exists('config') && method_exists('config', 'getOpenAiApiKey')) {
        $configVal = config::getOpenAiApiKey();
    }
} catch (Throwable $e) {
    $configVal = null;
}

function masked($v) {
    if (!$v) return null;
    $len = strlen($v);
    if ($len <= 8) return str_repeat('*', $len);
    return substr($v,0,4) . str_repeat('*', max(0,$len-8)) . substr($v,-4);
}

echo json_encode([
    'env_present' => ($env !== false && $env !== ''),
    'env_masked' => masked($env),
    'config_present' => ($configVal !== null && $configVal !== ''),
    'config_masked' => masked($configVal)
]);
