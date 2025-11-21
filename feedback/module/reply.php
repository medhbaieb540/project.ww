<?php
require '../config.php';

// Get form data
$feedback_id = $_POST['feedback_id'] ?? '';
$message = $_POST['message'] ?? '';
$author = 'TestUser'; // In real app, get from logged-in user session

// Validate input
if (empty($feedback_id) || empty($message)) {
    die("Error: Missing required fields");
}

// Sanitize inputs
$message = sanitize_input($message);

// Prepare SQL
$sql = "INSERT INTO replies (feedback_id, author, message) 
        VALUES (:feedback_id, :author, :message)";

$db = Config::getConnexion();

try {
    $query = $db->prepare($sql);
    $query->execute([
        'feedback_id' => $feedback_id,
        'author' => $author,
        'message' => $message
    ]);
    
    // Success - redirect back to feedback page
    header('Location: ../feedback.php?reply_success=1');
    exit();
    
} catch (Exception $e) {
    error_log("Error posting reply: " . $e->getMessage());
    die("Error: Unable to post reply. Please try again.");
}
?>
