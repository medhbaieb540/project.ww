<?php
session_start();

// Define base path
define('BASE_PATH', __DIR__);

// Autoload controllers and models
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controller/GameController.php';
require_once __DIR__ . '/controller/CategoryController.php';
require_once __DIR__ . '/controller/ReviewController.php';

// Get controller and action from URL
$controller = $_GET['controller'] ?? 'game';
$action = $_GET['action'] ?? 'index';

// Special route for back office
if ($action === 'backoffice' || $action === 'back-office') {
    $action = 'backOfficeIndex';
}

// Special route for dashboard
if ($action === 'dashboard') {
    $action = 'dashboard';
}

// Route to appropriate controller
switch ($controller) {
    case 'game':
        $gameController = new GameController();
        if (method_exists($gameController, $action)) {
            $gameController->$action();
        } else {
            header('Location: ' . BASE_URL . '/index.php?controller=game&action=index');
            exit;
        }
        break;
        
    case 'category':
        $categoryController = new CategoryController();
        if (method_exists($categoryController, $action)) {
            $categoryController->$action();
        } else {
            header('Location: ' . BASE_URL . '/index.php?controller=category&action=index');
            exit;
        }
        break;
        
    case 'review':
        $reviewController = new ReviewController();
        if (method_exists($reviewController, $action)) {
            $reviewController->$action();
        } else {
            header('Location: ' . BASE_URL . '/index.php?controller=review&action=index');
            exit;
        }
        break;
        
    default:
        header('Location: ' . BASE_URL . '/index.php?controller=game&action=index');
        exit;
}
?>

