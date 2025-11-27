<?php
require_once __DIR__ . '/../config.php';

// Get feedback ID
$id = $_POST['id'] ?? '';

// Validate input
if (empty($id)) {
    die("Error: Missing feedback ID");
}

// First delete all replies associated with this feedback
$sql_replies = "DELETE FROM replies WHERE feedback_id = :id";

// Then delete the feedback itself
$sql_feedback = "DELETE FROM feedback WHERE id = :id";

$db = Config::getConnexion();

try {
    // Start transaction
    $db->beginTransaction();
    
    // Delete replies first (foreign key constraint)
    $query_replies = $db->prepare($sql_replies);
    $query_replies->execute(['id' => $id]);
    
    // Delete feedback
    $query_feedback = $db->prepare($sql_feedback);
    $query_feedback->execute(['id' => $id]);
    
    // Commit transaction
    $db->commit();
    
    // Success - redirect back to feedback page
    header('Location: ../views/feedback/feedback.php?delete_success=1');
    exit();
    
} catch (Exception $e) {
    // Rollback on error
    $db->rollBack();
    error_log("Error deleting feedback: " . $e->getMessage());
    die("Error: Unable to delete feedback. Please try again.");
}
?>
