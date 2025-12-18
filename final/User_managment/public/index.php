<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path and URL
define('BASE_PATH', __DIR__);
define('BASE_URL', '/User_managment/public');

try {
    // Autoload controllers and models
    $db_file = __DIR__ . '/../config/db.php';
    if (!file_exists($db_file)) {
        die("Database config file not found: $db_file");
    }
    require_once $db_file;
    
    $game_controller_file = __DIR__ . '/../controller/GameController.php';
    if (!file_exists($game_controller_file)) {
        die("GameController file not found: $game_controller_file");
    }
    require_once $game_controller_file;
    
    $category_controller_file = __DIR__ . '/../controller/CategoryController.php';
    if (!file_exists($category_controller_file)) {
        die("CategoryController file not found: $category_controller_file");
    }
    require_once $category_controller_file;
    
    $review_controller_file = __DIR__ . '/../controller/ReviewController.php';
    if (!file_exists($review_controller_file)) {
        die("ReviewController file not found: $review_controller_file");
    }
    require_once $review_controller_file;
    
    // Check if pdo exists
    if (!isset($pdo)) {
        die("Database connection failed - PDO not initialized");
    }
} catch (Exception $e) {
    die("Error loading files: " . $e->getMessage());
}


// Get controller and action from URL
$controller = $_GET['controller'] ?? 'game';
$action = $_GET['action'] ?? 'index';

try {
    // Route to appropriate controller
    switch ($controller) {
        case 'game':
            $gameController = new GameController($pdo);
            if (method_exists($gameController, $action)) {
                $gameController->$action();
            } else {
                header('Location: index.php?controller=game&action=index');
                exit;
            }
            break;
            
        case 'category':
            $categoryController = new CategoryController($pdo);
            if (method_exists($categoryController, $action)) {
                $categoryController->$action();
            } else {
                header('Location: index.php?controller=category&action=index');
                exit;
            }
            break;
            
        case 'review':
            $reviewController = new ReviewController($pdo);
            if (method_exists($reviewController, $action)) {
                $reviewController->$action();
            } else {
                header('Location: index.php?controller=review&action=index');
                exit;
            }
            break;
            
        default:
            header('Location: index.php?controller=game&action=index');
            exit;
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n\nStack trace:\n" . $e->getTraceAsString());
}
?>

