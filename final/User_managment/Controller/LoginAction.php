<?php
// Controller/LoginAction.php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/UserController.php';

$userC = new UserController($pdo);

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === "" || $password === "") {
        $error = "Email or password cannot be empty";
    } else {
        try {
            $user = $userC->getUserByEmail($email);

            if (!$user) {
                $error = "Email not found";
            } elseif (!password_verify($password, $user['password'])) {
                $error = "Wrong password";
            } else {
                // ✅ Save session
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['username']   = $user['username'];
                $_SESSION['email']      = $user['email'];
                $_SESSION['user_role']  = $user['user_role'];
                $_SESSION['birth_date'] = $user['birth_date'] ?? null;
                $_SESSION['address']    = $user['address'] ?? null;
                $_SESSION['gender']     = $user['gender'] ?? null;

                // ✅ Redirect by role
                if (in_array($user['user_role'], ['admin','super_admin'])) {
                    header("Location: ../View/BackOffice/admin.php");
                } else {
                    header("Location: ../View/FrontOffice/profile.php");
                }
                exit;
            }
        } catch (Exception $e) {
            $error = "DB error: " . $e->getMessage();
        }
    }
}

// if you want: store error to show in login.php
if ($error !== "") {
    $_SESSION['login_error'] = $error;
    header("Location: ../View/FrontOffice/login.php");
    exit;
}
