<?php
session_set_cookie_params(['path' => '/FINAL/', 'httponly' => true]);
session_start();

if (!in_array($_SESSION['user_role'] ?? '', ['admin', 'super_admin'], true)) {
    header("Location: ../FrontOffice/login.php");
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../Controller/UserController.php';

$userC = new UserController($pdo);

// تأكد إن الإيدي موجود
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $userC->banUser($id);
}

// خليك بـ USERS section
$section = $_GET['section'] ?? 'users';

// ارجع للوحة التحكم
header("Location: admin.php?section=" . urlencode($section));
exit;
