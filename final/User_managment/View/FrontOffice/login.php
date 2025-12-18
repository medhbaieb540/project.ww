<?php
session_start();
$loginError = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login | GameZone</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../../public/css/style.css" />
</head>
<body>
  <div class="bg-orbit"></div>

  <div class="container">
    <div class="logo">
      <img class="logo-img" src="../../public/images/logo.jpg" alt="GameBridge Logo" />
      <div class="logo-text">GameBridge</div>
    </div>

    <div class="badge">
      <span class="icon">★</span>
      Members-only access to reviews & news
    </div>

    <h1>Welcome to GameBridge!</h1>
    <p class="subtitle">Log in to continue to your dashboard, reviews & game discussions.</p>

    <form id="loginForm" method="POST" action="../../Controller/LoginAction.php">
      <div>
        <div class="field-label">Email</div>
        <div class="input-wrap">
          <input type="text" id="username" name="email" placeholder="example@GameBridge.com" />
          <span class="input-icon">👤</span>
        </div>
      </div>

      <div>
        <div class="field-label">Password</div>
        <div class="input-wrap">
          <input type="password" id="password" name="password" placeholder="••••••••"/>
          <span class="input-icon">🔒</span>
        </div>
      </div>

      <div class="row">
        <label class="remember">
          <input type="checkbox" id="remember" />
          Remember me
        </label>
        <a href="forget_password.php" class="forgot">Forgot password?</a>
      </div>

      <button type="submit" class="btn-primary">Log In</button>

      <!-- ✅ Server-side error (from PHP session) -->
      <?php if (!empty($loginError)): ?>
        <div id="serverStatus" class="status error">
          <?= htmlspecialchars($loginError) ?>
        </div>
      <?php endif; ?>

      <!-- ✅ Client-side status (from JS validation) -->
     <div id="status" class="status" style="display:none;"></div>

      <div class="divider">
        <span></span><p>or continue with</p><span></span>
      </div>

      <div class="socials">
        <button type="button" class="social-btn"><span>G</span> Google</button>
        <button type="button" class="social-btn"><span></span> Apple</button>
        <button type="button" class="social-btn"><span>♛</span> Discord</button>
      </div>

      <p class="signup-text">
        New here?
        <a href="CreateAccount.php">Create account</a>
      </p>
    </form>
  </div>

  <!-- <script src="../../public/js/login.js?v=2"></script> -->
</body>
</html>
