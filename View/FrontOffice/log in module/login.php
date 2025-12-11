<?php
// login.php - Handle user login with database
session_start();
include __DIR__ . '/../../../config.php';

$status = '';
$statusClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $returnTo = isset($_GET['return_to']) ? $_GET['return_to'] : '../index.html';

  if (!$username || !$password) {
    $status = 'Please fill in all fields.';
    $statusClass = 'error';
  } else {
    try {
      $pdo = config::getConnexion();
      $stmt = $pdo->prepare('SELECT id, username, email, password FROM users WHERE username = ? OR email = ?');
      $stmt->execute([$username, $username]);
      $user = $stmt->fetch();

      if ($user && password_verify($password, $user['password'])) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $status = 'Login successful! Redirecting...';
        $statusClass = 'success';
        header('Location: ' . $returnTo, true, 303);
        exit();
      } else {
        $status = 'Invalid username or password.';
        $statusClass = 'error';
      }
    } catch (Exception $e) {
      $status = 'Database error: ' . $e->getMessage();
      $statusClass = 'error';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login | GameBridge</title>
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

    <div class="badge">
      <span class="icon">★</span>
      Members-only access to reviews & news
    </div>

  <h1>Welcome to GameBridge!</h1>
    <p class="subtitle">Log in to continue to your dashboard, reviews & game discussions.</p>

    <form id="loginForm" method="POST" action="">
      <div>
        <div class="field-label">Email or Username</div>
        <div class="input-wrap">
          <input type="text" id="username" name="username" placeholder="example@GameBridge.com" required />
          <span class="input-icon">👤</span>
        </div>
      </div>

      <div>
        <div class="field-label">Password</div>
        <div class="input-wrap">
          <input type="password" id="password" name="password" placeholder="••••••••" required />
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
      <?php if ($status): ?>
        <div id="status" class="status <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($status); ?></div>
      <?php else: ?>
        <div id="status" class="status"></div>
      <?php endif; ?>

      <div class="divider">
        <span></span><p>or continue with</p><span></span>
      </div>

      <div class="socials">
        <button type="button" class="social-btn"><span>G</span> Google</button>
        <button type="button" class="social-btn"><span></span> Apple</button>
        <button type="button" class="social-btn"><span>♛</span> Discord</button>
      </div>

      <p class="signup-text">
        New here?
        <a href="create_account.php">Create account</a>
      </p>
    </form>
  </div>

  <script>
    // Demo: social buttons (placeholder)
    document.querySelectorAll('.social-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        alert('Social login coming soon!');
      });
    });
  </script>
</body>
</html>
