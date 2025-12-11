<?php
// create_account.php - Handle user registration with database
session_start();
include __DIR__ . '/../../../config.php';

$status = '';
$statusClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['signupUsername'] ?? '');
  $email = trim($_POST['signupEmail'] ?? '');
  $password = $_POST['signupPassword'] ?? '';
  $confirm = $_POST['signupConfirm'] ?? '';
  $terms = isset($_POST['terms']) ? 1 : 0;
  $role = trim($_POST['roleSelect'] ?? '');

  // Validation
  if (!$username || !$email || !$password || !$confirm) {
    $status = 'Please fill in all fields.';
    $statusClass = 'error';
  } elseif (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email)) {
    $status = 'Please enter a valid email address.';
    $statusClass = 'error';
  } elseif (strlen($password) < 8) {
    $status = 'Password must be at least 8 characters.';
    $statusClass = 'error';
  } elseif ($password !== $confirm) {
    $status = 'Passwords do not match.';
    $statusClass = 'error';
  } elseif (!$terms) {
    $status = 'You must agree to the terms to create an account.';
    $statusClass = 'error';
  } elseif (!$role || !in_array($role, ['player', 'developer'])) {
    $status = 'Please select a valid account type (Player or Developer).';
    $statusClass = 'error';
  } else {
    // Try to register
    try {
      $pdo = config::getConnexion();
      
      // Check if user already exists
      $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
      $stmt->execute([$username, $email]);
      if ($stmt->fetch()) {
        $status = 'Username or email already in use.';
        $statusClass = 'error';
      } else {
        // Hash password and insert
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$username, $email, $hashedPassword, $role]);
        
        $status = 'Account created successfully! Please log in.';
        $statusClass = 'success';
        
        // Redirect to login after 2 seconds
        echo '<script>setTimeout(() => { window.location.href = "login.php"; }, 2000);</script>';
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
  <title>Create Account | GameBridge</title>
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

    <h1>Create your account</h1>
    <p class="subtitle">Join GameBridge to post reviews, follow discussions and get personalised recommendations.</p>

    <form id="signupForm" method="POST" action="">
      <div>
        <div class="field-label">Username</div>
        <div class="input-wrap">
          <input type="text" id="signupUsername" name="signupUsername" placeholder="gamer123" required />
          <span class="input-icon">👤</span>
        </div>
      </div>

      <div>
        <div class="field-label">Email</div>
        <div class="input-wrap">
          <input type="email" id="signupEmail" name="signupEmail" placeholder="you@example.com" required />
          <span class="input-icon">✉️</span>
        </div>
      </div>

      <div>
        <div class="field-label">Password</div>
        <div class="input-wrap">
          <input type="password" id="signupPassword" name="signupPassword" placeholder="Create a strong password" required />
          <span class="input-icon">🔒</span>
        </div>
      </div>

      <div>
        <div class="field-label">Confirm password</div>
        <div class="input-wrap">
          <input type="password" id="signupConfirm" name="signupConfirm" placeholder="Repeat password" required />
          <span class="input-icon">🔒</span>
        </div>
      </div>

      <div>
        <div class="field-label">Account type</div>
        <div class="input-wrap">
          <select name="roleSelect" id="roleSelect" required>
            <option value="" disabled selected hidden>Select account type</option>
            <option value="player">🎮 Player</option>
            <option value="developer">💻 Developer</option>
          </select>
        </div>
      </div>

      <label class="remember" style="margin:10px 0; display:flex; align-items:center; gap:8px;">
        <input type="checkbox" id="terms" name="terms" />
        I agree to the <a href="#">Terms of Service</a> & <a href="#">Privacy Policy</a>
      </label>

      <button type="submit" class="btn-primary">Create account</button>

      <?php if ($status): ?>
        <div id="signupStatus" class="status <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($status); ?></div>
      <?php else: ?>
        <div id="signupStatus" class="status"></div>
      <?php endif; ?>

      <p class="signup-text">
        Already have an account?
        <a href="login.php">Log in</a>
      </p>

      <p class="signup-text">
        Forgot password?
        <a href="forget_password.php">Reset it</a>
      </p>
    </form>
  </div>
</body>
</html>
