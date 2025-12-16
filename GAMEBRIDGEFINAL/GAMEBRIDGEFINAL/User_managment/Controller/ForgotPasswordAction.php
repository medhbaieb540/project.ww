<?php
// Controller/ForgotPasswordAction.php

require_once __DIR__ . '/ResetPasswordController.php';

$reset = new ResetPasswordController();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['email']) && !empty($_POST['email'])) {

            $email = $_POST['email'];

            // نرسل طلب reset
            $reset->requestReset($email);

            // رسالة موحدة حتى لو الإيميل غير موجود (للأمان)
            $message = "If this email exists, a reset link has been sent.";
        }
    } catch (Exception $e) {
        echo 'Error :' . $e->getMessage();
    }
}
