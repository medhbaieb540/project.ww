<?php
// delete.php
require_once __DIR__ . '/../../Model/User.php';
require_once __DIR__ . '/../../controller/UserController.php';

if (!isset($_GET['id'])) {
    header('Location: UserList.php');
    exit;
}

$id = (int) $_GET['id'];

$userC = new UserController();

try {
    $userC->deleteUser($id);   // حذف من قاعدة البيانات
} catch (Exception $e) {
    // ممكن تخزن الرسالة في سشن وتعرضها في UserList لو حاب
    // $_SESSION['error'] = $e->getMessage();
}

// رجوع إلى صفحة عرض المستخدمين
header('Location: UserList.php');
exit;
