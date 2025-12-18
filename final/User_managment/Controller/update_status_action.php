<?php
session_start();
header('Content-Type: application/json');

$role = $_SESSION['user_role'] ?? ($_SESSION['role'] ?? 'player');
if (!in_array($role, ['developer', 'admin'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Insufficient permissions.']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/FeedbackController.php';

$id = isset($_POST['feedback_id']) ? (int) $_POST['feedback_id'] : (int) ($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid feedback id.']);
    exit;
}

try {
    $controller = new FeedbackController($pdo);
    $controller->updateStatus($id, $status, $role);

    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully.',
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
