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

$controller = new FeedbackController($pdo);

$game = $_POST['game'] ?? '';
$type = $_POST['type'] ?? 'feedback';
$message = $_POST['message'] ?? '';
$status = $_POST['status'] ?? null;

$author = $_SESSION['username'] ?? 'anonymous';
$role = $_SESSION['user_role'] ?? ($_SESSION['role'] ?? 'player');

try {
    $feedbackId = $controller->createFeedback($game, $type, $message, $author, $role, $status);
    echo json_encode([
        'success' => true,
        'message' => 'Feedback submitted successfully.',
        'id'      => $feedbackId,
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
