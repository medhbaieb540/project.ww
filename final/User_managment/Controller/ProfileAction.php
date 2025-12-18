<?php
// Controller/ProfileAction.php

session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/UserController.php';

$userC = new UserController($pdo);


// تأكد إن فيه user عامل تسجيل دخول
if (!isset($_SESSION['user_id'])) {
    header("Location: ../View/FrontOffice/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$user   = $userC->getUserById($userId);

if (!$user) {
    die("User not found");
}

$role = $user['user_role']; // player أو developer أو admin مثلاً
