<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$senderId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

if ($senderId === null) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/CommunityController.php';

$receiverId = (int) ($_POST['receiver_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if ($receiverId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid receiver']);
    exit;
}

try {
    $controller = new CommunityController($pdo);
    $id = $controller->sendMessage((int) $senderId, $receiverId, $message);
    echo json_encode(['success' => true, 'message_id' => $id]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
