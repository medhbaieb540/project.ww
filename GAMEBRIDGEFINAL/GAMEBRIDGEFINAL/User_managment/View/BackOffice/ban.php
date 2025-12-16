<?php
require_once '../../controller/UserController.php';

$userC = new UserController();

// تأكد إن الإيدي موجود
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $userC->banUser($id);
}

// خليك بـ USERS section
$section = $_GET['section'] ?? 'users';

// ارجع للوحة التحكم
header("Location: admin.php?section=" . urlencode($section));
exit;
