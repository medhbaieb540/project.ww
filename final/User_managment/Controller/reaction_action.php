<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$username = $_SESSION['username'] ?? null;
if ($username === null) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/CommunityController.php';

$target = trim($_POST['target'] ?? '');
$type = trim($_POST['type'] ?? '');

if ($target === '' || $type === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid inputs']);
    exit;
}

try {
    $controller = new CommunityController($pdo);
    $counts = $controller->toggleReaction($target, $username, $type);
    echo json_encode(['success' => true, 'counts' => $counts]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
