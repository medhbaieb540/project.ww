<?php
// View/BackOffice/update.php

session_set_cookie_params(['path' => '/FINAL/', 'httponly' => true]);
session_start();

if (!in_array($_SESSION['user_role'] ?? '', ['admin', 'super_admin'], true)) {
    header("Location: ../FrontOffice/login.php");
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$currentRole = $_SESSION['user_role'] ?? '';

// ✅ get id from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid user id.");
}

// ✅ load user from DB
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update User</title>
<link rel="stylesheet" href="../../public/css/update_user.css">
</head>
<body>

<h2 class="title">Update User</h2>

<form method="POST" action="../../Controller/UpdateUserAction.php?id=<?= urlencode((string)$id) ?>">

    <label>Username</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>">

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">

    <?php if ($currentRole === 'super_admin'): ?>
    <label>New Password (optional)</label>
    <input type="password" name="password" placeholder="Enter new password">
    <?php endif; ?>

    <label>User Role</label>
    <select name="user_role">
        <option value="admin" <?= (($user['user_role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
        <option value="player" <?= (($user['user_role'] ?? '') === 'player') ? 'selected' : '' ?>>Player</option>
        <option value="developer" <?= (($user['user_role'] ?? '') === 'developer') ? 'selected' : '' ?>>Developer</option>
    </select>

    <label>Birth Date</label>
    <input type="date" name="birthdate" value="<?= htmlspecialchars($user['birth_date'] ?? '') ?>">

    <label>Address</label>
    <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>">

    <label>Gender</label>
    <select name="gender">
        <option value="male" <?= (($user['gender'] ?? '') === 'male') ? 'selected' : '' ?>>Male</option>
        <option value="female" <?= (($user['gender'] ?? '') === 'female') ? 'selected' : '' ?>>Female</option>
    </select>

    <button type="submit">Update</button>
</form>

</body>
</html>
