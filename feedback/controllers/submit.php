<?php
require_once __DIR__ . '/../config.php';

// Get form data
$game = $_POST['game'] ?? '';
$type = $_POST['type'] ?? '';
$message = $_POST['message'] ?? '';
$author = 'TestUser'; // In real app, get from logged-in user session

// Validate input
if (empty($game) || empty($type) || empty($message)) {
    die("Error: All fields are required");
}

// Validate feedback type
if (!in_array($type, ['feedback', 'report'])) {
    die("Error: Invalid feedback type");
}

// Sanitize inputs
$game = sanitize_input($game);
$message = sanitize_input($message);

// Prepare SQL
$sql = "INSERT INTO feedback (game, type, message, author) 
        VALUES (:game, :type, :message, :author)";

$db = Config::getConnexion();

try {
    $query = $db->prepare($sql);
    $query->execute([
        'game' => $game,
        'type' => $type,
        'message' => $message,
        'author' => $author
    ]);
    
    // Success - redirect back to feedback page
    header('Location: ../views/feedback/feedback.php?delete_success=1');
    exit();
    
} catch (Exception $e) {
    error_log("Error submitting feedback: " . $e->getMessage());
    die("Error: Unable to submit feedback. Please try again.");
}
?>
