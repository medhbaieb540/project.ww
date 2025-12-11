<?php
// forget_password.php - Handle password reset request
session_start();
include __DIR__ . '/../../../config.php';

$status = '';
$statusClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['resetEmail'] ?? '');

  if (!$email) {
    $status = 'Please enter your email address.';
    $statusClass = 'error';
  } elseif (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email)) {
    $status = 'Please enter a valid email address.';
    $statusClass = 'error';
  } else {
    try {
      $pdo = config::getConnexion();
      $stmt = $pdo->prepare('SELECT id, username FROM users WHERE email = ?');
      $stmt->execute([$email]);
      $user = $stmt->fetch();

      if ($user) {
        // Generate a reset token and store it (simplified - no token expiry in this version)
        $resetToken = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare('UPDATE users SET reset_token = ? WHERE id = ?');
        $stmt->execute([$resetToken, $user['id']]);
        
        // In production, send email with reset link:
        // $resetLink = "https://yoursite.com/reset_password.php?token=" . $resetToken;
        
        $status = 'If an account with that email exists, a password reset link has been sent.';
        $statusClass = 'success';
      } else {
        // Generic message for security (don't reveal if email exists)
        $status = 'If an account with that email exists, a password reset link has been sent.';
        $statusClass = 'success';
      }
    } catch (Exception $e) {
      $status = 'Error: ' . $e->getMessage();
      $statusClass = 'error';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Reset Password | GameBridge</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="bg-orbit"></div>

  <div class="container">
    <div class="logo">
      <img class="logo-img" src="../images/logo.png" alt="GameBridge Logo" />
      <div class="logo-text">GameBridge</div>
    </div>

    <h1>Forgot your password?</h1>
    <p class="subtitle">Enter the email address on your account and we'll send a link to reset your password.</p>

    <form id="resetForm" method="POST" action="">
      <div>
        <div class="field-label">Email address</div>
        <div class="input-wrap">
          <input type="email" id="resetEmail" name="resetEmail" placeholder="you@example.com" required />
          <span class="input-icon">✉️</span>
        </div>
      </div>

      <button type="submit" class="btn-primary">Send Reset Link</button>

      <?php if ($status): ?>
        <div id="resetStatus" class="status <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($status); ?></div>
      <?php else: ?>
        <div id="resetStatus" class="status"></div>
      <?php endif; ?>

      <p class="signup-text">
        Remembered your password?
        <a href="login.php">Back to login</a>
      </p>
    </form>
  </div>
</body>
</html>
