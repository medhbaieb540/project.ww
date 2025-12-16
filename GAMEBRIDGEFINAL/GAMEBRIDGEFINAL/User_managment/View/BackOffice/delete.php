<?php
require_once '../../Controller/UserController.php';
require_once '../../config.php';

if (!isset($_GET['id'])) {
    header('Location: admin.php?section=users');
    exit;
}

$id = (int) $_GET['id'];

$db = config::getConnexion();

// نتحقق أولاً هل المستخدم banned ولا لا
$stmt = $db->prepare("SELECT is_banned FROM users WHERE id = :id");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // مستخدم غير موجود
    header('Location: admin.php?section=users');
    exit;
}

if ((int)$user['is_banned'] !== 1) {
    header('Location: admin.php?section=users');
    exit;
}

$userC = new UserController();

try {
    $userC->deleteUser($id);
} catch (Exception $e) {
    // لو تحب تخزن error في session لاحقًا، أضف session_start() فوق
}

header('Location: admin.php?section=users');
exit;
