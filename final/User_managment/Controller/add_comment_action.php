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

$postId = (int) ($_POST['post_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid post id']);
    exit;
}

try {
    $controller = new CommunityController($pdo);
    $commentId = $controller->createComment($postId, (int) $userId, $content);
    $comment = $controller->getPostWithDetails($postId);
    echo json_encode([
        'success' => true,
        'comment_id' => $commentId,
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
