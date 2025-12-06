<?php
session_start();

require_once '../../controller/UserController.php';


$userC = new UserController();

$currentRole = $_SESSION['user_role'] ?? null;

if (!isset($_GET['id'])) {
    die("ERROR: Missing ID in URL");
}

$id = intval($_GET['id']);

$user = $userC->getUserById($id);

if (!$user) {
    die("ERROR: User not found");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = $_POST['username'];
    $email     = $_POST['email'];
    $role      = $_POST['user_role'];
    $birth_date= $_POST['birthdate'];
    $address   = $_POST['address'];
    $gender    = $_POST['gender'];


    
    
    $userC->updateUser($id, $username,$email, $role,$birth_date,$address,$gender);

    // لو سوبر أدمن وكتب باسورد جديد
    if ($currentRole === 'super_admin' && !empty($_POST['password'])) {
        
        $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $userC->updateUserPassword($id, $hashed); // دالة خاصة بس للباسورد
    }

    header("Location: ../FrontOffice/admin.php?section=users");
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
    <input type="text" name="username" value="<?= $user['username'] ?>">

    <label>Email</label>
    <input type="email" name="email" value="<?= $user['email'] ?>">
    
    <?php if ($currentRole === 'super_admin'): ?>
    <label>New Password (optional)</label>
    <input type="password" name="password" placeholder="Enter new password">
    <?php endif; ?>


    <label>User Role</label>
    <select name="user_role">
        <option value ="admin" <?= $user['user_role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="player"   <?= $user['user_role']=='player' ? 'selected' : '' ?>>Player</option>
        <option value="developer" <?= $user['user_role']=='developer' ? 'selected' : '' ?>>Developer</option>
    </select>

    <label>Birth Date</label>
    <input type="date" name="birthdate" value="<?= $user['birth_date'] ?>">

    <label>Address</label>
    <input type="text" name="address" value="<?= $user['address'] ?>">

    <label>Gender</label>
    <select name="gender">
    <option value="male"   <?= $user['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
    <option value="female" <?= $user['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
    </select>



    <button type="submit">Update</button>
</form>

</body>
</html>
