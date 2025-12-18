<?php
// Controller/UpdateUserAction.php

session_set_cookie_params(['path' => '/FINAL/', 'httponly' => true]);
session_start();

if (!in_array($_SESSION['user_role'] ?? '', ['admin', 'super_admin'], true)) {
    header("Location: ../View/FrontOffice/login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';

if (!isset($_GET['id'])) {
    die("ERROR: Missing ID in URL");
}

$id = (int)$_GET['id'];
if ($id <= 0) {
    die("ERROR: Invalid ID");
}

$currentRole = $_SESSION['user_role'] ?? null;

// ✅ Only handle POST here
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/BackOffice/update.php?id=" . urlencode((string)$id));
    exit;
}

// ✅ Read fields
$username   = trim($_POST['username'] ?? '');
$email      = trim($_POST['email'] ?? '');
$role       = trim($_POST['user_role'] ?? '');
$birth_date = $_POST['birthdate'] ?? null;
$address    = trim($_POST['address'] ?? '');
$gender     = trim($_POST['gender'] ?? '');

// ✅ Basic validation
$errors = [];
if ($username === '') $errors[] = "Username is required.";
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
if (!in_array($role, ['admin','player','developer'], true)) $errors[] = "Invalid role.";
if (!in_array($gender, ['male','female'], true)) $errors[] = "Invalid gender.";

if (!empty($errors)) {
    // simplest: stop + show message (you can later redirect with session flash)
    die("ERROR: " . implode(" | ", $errors));
}

try {
    // ✅ Update user main fields
    $stmt = $pdo->prepare("
        UPDATE users
        SET username = :username,
            email = :email,
            user_role = :role,
            birth_date = :birth_date,
            address = :address,
            gender = :gender
        WHERE id = :id
    ");
    $stmt->execute([
        ':username'   => $username,
        ':email'      => $email,
        ':role'       => $role,
        ':birth_date' => $birth_date,
        ':address'    => $address,
        ':gender'     => $gender,
        ':id'         => $id
    ]);

    // ✅ super_admin can update password (optional)
    if ($currentRole === 'super_admin' && !empty($_POST['password'])) {
        $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt2 = $pdo->prepare("UPDATE users SET password = :pass WHERE id = :id");
        $stmt2->execute([
            ':pass' => $hashed,
            ':id'   => $id
        ]);
    }

    header("Location: ../View/BackOffice/admin.php?section=users");
    exit;

} catch (Exception $e) {
    die("ERROR: Update failed.");
}
