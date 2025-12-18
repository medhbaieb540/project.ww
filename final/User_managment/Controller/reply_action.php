<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/FeedbackController.php';

$feedbackId = isset($_POST['feedback_id']) ? (int) $_POST['feedback_id'] : 0;
$message = $_POST['message'] ?? '';
$author = $_SESSION['username'] ?? 'anonymous';

if ($feedbackId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid feedback id.']);
    exit;
}

try {
    $controller = new FeedbackController($pdo);
    $controller->addReply($feedbackId, $author, $message);

    echo json_encode([
        'success' => true,
        'message' => 'Reply added successfully.',
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
