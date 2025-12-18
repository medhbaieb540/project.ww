<?php
// Controller/ResetPasswordAction.php

require_once __DIR__ . '/ResetPasswordController.php';
session_start();

$reset = new ResetPasswordController($pdo);
$message = "";

// يجب أن تصل token + email عبر الرابط
if (!isset($_GET['token'], $_GET['email'])) {
    die("Invalid reset link.");
}

$token = $_GET['token'];
$email = $_GET['email'];

// عند إرسال الباسورد الجديد
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        isset($_POST['new_password'], $_POST['confirm_password']) &&
        !empty($_POST['new_password']) &&
        !empty($_POST['confirm_password'])
    ) {

        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $message = "Passwords do not match.";
        } else {
            $newPass = $_POST['new_password'];

            // محاولة تحديث كلمة المرور
            $ok = $reset->resetPassword($email, $token, $newPass);

            if ($ok) {
                $message = "Password changed successfully. You can now log in.";
            } else {
                $message = "Invalid or expired reset link.";
            }
        }
    } else {
        $message = "Fill all fields.";
    }
}
