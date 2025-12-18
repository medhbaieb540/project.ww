<?php
// View/FrontOffice/reset_password.php
require_once __DIR__ . '/../../Controller/ResetPasswordAction.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Set New Password | GameBridge</title>
  <link rel="stylesheet" href="../../public/css/style.css" />
</head>
<body>
  <div class="bg-orbit"></div>

  <div class="container">
    <div class="logo">
      <img class="logo-img" src="../../public/images/logo.jpg" alt="GameBridge Logo" />
      <div class="logo-text">GameBridge</div>
    </div>

    <h1>Set a new password</h1>
    <p class="subtitle">Enter your new password below.</p>

    <form id="passwordForm" method="POST" action="../../Controller/ResetPasswordAction.php?token=<?= urlencode($token) ?>&email=<?= urlencode($email) ?>">
      <div>
        <div class="field-label">New password</div>
        <div class="input-wrap">
          <input type="password" id="new_password" name="new_password" placeholder="New password" />
        </div>
        <div id="PasswordStatus" class="status"></div>
      </div>

      <div>
        <div class="field-label">Confirm password</div>
        <div class="input-wrap">
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" />
        </div>
        <div id="ConfirmStatus" class="status"></div>
      </div>

      <button type="submit" class="btn-primary">Reset Password</button>

      <div id="signupStatus" class="status">
        <?php if (!empty($message)) echo $message; ?>
      </div>

      <p class="signup-text">
        Remembered your password?
        <a href="login.php">Back to login</a>
      </p>
    </form>
  </div>

  <script src="../../public/js/reset_password.js"></script>
</body>
</html>
