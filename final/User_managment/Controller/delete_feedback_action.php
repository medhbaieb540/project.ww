<?php
session_start();
header('Content-Type: application/json');

$role = $_SESSION['user_role'] ?? ($_SESSION['role'] ?? 'player');
if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only admins can delete feedback.']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/FeedbackController.php';

$id = isset($_POST['feedback_id']) ? (int) $_POST['feedback_id'] : (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid feedback id.']);
    exit;
}

try {
    $controller = new FeedbackController($pdo);
    $controller->deleteFeedback($id, $role);

    echo json_encode([
        'success' => true,
        'message' => 'Feedback deleted successfully.',
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
