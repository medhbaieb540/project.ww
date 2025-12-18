<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
if ($userId === null) {
    header('Location: ../View/FrontOffice/login.php');
    exit();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/CommunityController.php';

$controller = new CommunityController($pdo);

$content = trim($_POST['content'] ?? '');
$category = trim($_POST['category'] ?? 'General');
$imageName = null;

if (isset($_FILES['image']) && is_array($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024;
    $type = $_FILES['image']['type'] ?? '';
    $size = (int) ($_FILES['image']['size'] ?? 0);

    if (!in_array($type, $allowed, true)) {
        $_SESSION['flash_error'] = 'Invalid image type. Only JPG, PNG, GIF allowed.';
        header('Location: ../View/FrontOffice/community.php');
        exit();
    }

    if ($size > $maxSize) {
        $_SESSION['flash_error'] = 'Image too large. Max 5MB allowed.';
        header('Location: ../View/FrontOffice/community.php');
        exit();
    }

    $uploadDir = __DIR__ . '/../public/images/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['image']['name']));
    $target = $uploadDir . $safeName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $imageName = $safeName;
    }
}

try {
    $controller->createPost((int) $userId, $content, $category, $imageName);
    header('Location: ../View/FrontOffice/community.php');
    exit();
} catch (Throwable $e) {
    $_SESSION['flash_error'] = $e->getMessage();
    header('Location: ../View/FrontOffice/community.php');
    exit();
}
