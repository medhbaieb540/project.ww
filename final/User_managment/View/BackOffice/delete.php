<?php
// View/BackOffice/delete.php

session_set_cookie_params(['path' => '/FINAL/', 'httponly' => true]);
session_start();

if (!in_array($_SESSION['user_role'] ?? '', ['admin', 'super_admin'], true)) {
    header("Location: ../FrontOffice/login.php");
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../Controller/UserController.php';

if (!isset($_GET['id'])) {
    header('Location: admin.php?section=users');
    exit;
}

$id = (int)$_GET['id'];
if ($id <= 0) {
    header('Location: admin.php?section=users');
    exit;
}

// ✅ check first if user exists + is banned
$stmt = $pdo->prepare("SELECT is_banned FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: admin.php?section=users');
    exit;
}

if ((int)($user['is_banned'] ?? 0) !== 1) {
    // not banned => don't allow delete
    header('Location: admin.php?section=users');
    exit;
}

// ✅ now delete
$userC = new UserController($pdo);

try {
    $userC->deleteUser($id);
} catch (Exception $e) {
    // you can log later if you want
}

header('Location: admin.php?section=users');
exit;
