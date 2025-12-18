<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$role = $_SESSION['user_role'] ?? ($_SESSION['role'] ?? 'player');

if ($userId === null) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/CommunityController.php';

$commentId = (int) ($_POST['comment_id'] ?? 0);

if ($commentId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid comment id']);
    exit;
}

try {
    $controller = new CommunityController($pdo);
    $controller->deleteComment($commentId, (int) $userId, $role);
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
