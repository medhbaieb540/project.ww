<?php

require_once '../../Controller/ResetPasswordController.php';

session_start();

$reset = new ResetPasswordController();
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
        isset($_POST['password'], $_POST['confirm_password']) &&
        !empty($_POST['password']) &&
        !empty($_POST['confirm_password'])
    ) {

        if ($_POST['password'] !== $_POST['confirm_password']) {
            $message = "Passwords do not match.";
        } else {
            $newPass = $_POST['password'];

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

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Set New Password | GameBridge</title>
  <link rel="stylesheet" href="../FrontOffice/style.css" />
</head>
<body>
  <div class="bg-orbit"></div>

  <div class="container">
    <div class="logo">
      <img class="logo-img" src="logo.jpg" alt="GameBridge Logo" />
      <div class="logo-text">GameBridge</div>
    </div>

    <h1>Set a new password</h1>
    <p class="subtitle">Enter your new password below.</p>

    <?php if (!empty($message)): ?>
        <div class="status"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div>
        <div class="field-label">New password</div>
        <div class="input-wrap">
          <input type="password" name="password" placeholder="New password" required />
        </div>
      </div>

      <div>
        <div class="field-label">Confirm password</div>
        <div class="input-wrap">
          <input type="password" name="confirm_password" placeholder="Confirm password" required />
        </div>
      </div>

      <button type="submit" class="btn-primary">Reset Password</button>

      <p class="signup-text">
        Remembered your password?
        <a href="login.php">Back to login</a>
      </p>
    </form>
  </div>
</body>
</html>
