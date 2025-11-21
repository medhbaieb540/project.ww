<?php


require_once '../../controller/UserController.php';
require_once '../../config.php';

$userC = new UserController();

// 1) تأكد أن id وصل
if (!isset($_GET['id'])) {
    die("ERROR: Missing ID in URL");
}

$id = intval($_GET['id']);

// 2) اجلب بيانات المستخدم
$user = $userC->getUserById($id);

if (!$user) {
    die("ERROR: User not found");
}

// 3) إذا أرسل الفورم → نعمل تحديث
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = $_POST['username'];
    $email     = $_POST['email'];
    $password  = $_POST['password'];
    $role      = $_POST['user_role'];

    // استدعاء دالة التحديث
    $userC->updateUser($id, $username, $email, $password, $role);

    header("Location: UserList.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update User</title>
<style>
    body { background:#0c0c0c; color:white; font-family:Poppins,sans-serif; }
    form { width:400px; margin:80px auto; background:#111; padding:30px; border-radius:10px; }
    input, select {
        width:100%; padding:10px; margin:10px 0;
        border-radius:6px; border:1px solid #1aff87;
        background:#222; color:white;
    }
    button {
        width:100%; padding:10px;
        background:#1aff87; border:none;
        border-radius:6px; font-size:16px; cursor:pointer;
    }
</style>
</head>
<body>

<h2 style="text-align:center; color:#1aff87;">Update User</h2>

<form method="POST">

    <label>Username</label>
    <input type="text" name="username" value="<?= $user['username'] ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= $user['email'] ?>" required>

    <label>Password</label>
    <input type="text" name="password" value="<?= $user['password'] ?>" required>

    <label>User Role</label>
    <select name="user_role">
        <option value="player"   <?= $user['user_role']=='player' ? 'selected' : '' ?>>Player</option>
        <option value="developer" <?= $user['user_role']=='developer' ? 'selected' : '' ?>>Developer</option>
    </select>

    <button type="submit">Update</button>
</form>

</body>
</html>
