<?php
// Controller/UserListAction.php
session_start();

require_once __DIR__ . '/UserController.php';
require_once __DIR__ . '/../config.php';

$userC = new UserController();
$users = $userC->listUsers();   // جلب المستخدمين من الداتابيس

$currentRole = $_SESSION['user_role'] ?? null;
