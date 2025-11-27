<?php
require_once __DIR__ . '/../config.php';

// Get form data
$id = $_POST['id'] ?? '';
$status = $_POST['status'] ?? '';

// Validate input
if (empty($id) || empty($status)) {
    die("Error: Missing required fields");
}

// Validate status value
if (!in_array($status, ['pending', 'reviewed', 'fixed'])) {
    die("Error: Invalid status value");
}

// Prepare SQL
$sql = "UPDATE feedback SET status = :status WHERE id = :id";

$db = Config::getConnexion();

try {
    $query = $db->prepare($sql);
    $query->execute([
        'status' => $status,
        'id' => $id
    ]);
    
    // Success - redirect back to feedback page
    header('Location: ../views/feedback/feedback.php?delete_success=1');
    exit();
    
} catch (Exception $e) {
    error_log("Error updating feedback: " . $e->getMessage());
    die("Error: Unable to update status. Please try again.");
}
?>
