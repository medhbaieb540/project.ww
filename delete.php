<?php
require_once '../../Controller/UserController.php';
require_once '../../config.php';

if (!isset($_GET['id'])) {
    header('Location: ../FrontOffice/admin.php?section=users');
    exit;
}

$id = (int) $_GET['id'];

$db = config::getConnexion();

// نتحقق أولاً هل المستخدم banned ولا لا
$stmt = $db->prepare("SELECT is_banned FROM login WHERE id = :id");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // مستخدم غير موجود
    header('Location: ../FrontOffice/admin.php?section=users');
    exit;
}

if ((int)$user['is_banned'] !== 1) {
    // مش محظور → ما نسمح بالحذف
    // ممكن تبعت رسالة خطأ لو حاب
    // مثلا باستخدام session
    // $_SESSION['error'] = "You must ban the user before deleting.";
    header('Location: ../FrontOffice/admin.php?section=users');
    exit;
}

$userC = new UserController();

try {
    $userC->deleteUser($id);
} catch (Exception $e) {
    // $_SESSION['error'] = $e->getMessage();
}

header('Location: ../FrontOffice/admin.php?section=users');
exit;
?>
