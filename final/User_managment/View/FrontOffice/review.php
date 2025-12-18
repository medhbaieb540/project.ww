<?php
// review.php - Handle review creation
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define BASE_URL for the review controller
define('BASE_URL', '/User_managment/public');

try {
    $db_file = __DIR__ . '/../../config/db.php';
    if (!file_exists($db_file)) {
        die("Database config file not found: $db_file");
    }
    require_once $db_file;
    
    $review_controller_file = __DIR__ . '/../../Controller/ReviewController.php';
    if (!file_exists($review_controller_file)) {
        die("ReviewController file not found: $review_controller_file");
    }
    require_once $review_controller_file;
    
    if (!isset($pdo)) {
        die("Database connection failed - PDO not initialized");
    }
} catch (Exception $e) {
    die("Error loading files: " . $e->getMessage());
}

$action = $_GET['action'] ?? 'create';
$reviewController = new ReviewController($pdo);

try {
    if (method_exists($reviewController, $action)) {
        $reviewController->$action();
    } else {
        $_SESSION['error'] = 'Action not found';
        header('Location: list.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
    header('Location: list.php');
    exit;
}
?>
