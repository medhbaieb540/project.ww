<?php
session_start();

// Handle role change
if (isset($_GET['role'])) {
    $role = $_GET['role'];
    $valid_roles = ['player', 'developer', 'admin'];
    
    if (in_array($role, $valid_roles)) {
        $_SESSION['role'] = $role;
        $_SESSION['username'] = ucfirst($role) . 'User';
        header('Location: feedback.php');
        exit();
    }
}

$current_role = $_SESSION['role'] ?? 'player';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Role Switcher - GameBridge</title>
 <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: #0c0c0c;
      color: #fff;
      font-family: 'Arial', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .container {
      background: #1a1a1a;
      border: 2px solid #1aff87;
      border-radius: 15px;
      padding: 40px;
      max-width: 500px;
      width: 100%;
      box-shadow: 0 0 30px #1aff8733;
    }

    h1 {
      color: #1aff87;
      text-align: center;
      margin-bottom: 10px;
      font-size: 2rem;
    }

    .subtitle {
      text-align: center;
      color: #aaa;
      margin-bottom: 30px;
      font-size: 0.9rem;
    }

    .current-role {
      background: #111;
      border: 1px solid #1aff8730;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 30px;
      text-align: center;
    }

    .current-role h3 {
      color: #1aff87;
      margin-bottom: 10px;
      font-size: 1.2rem;
    }

    .role-badge {
      display: inline-block;
      padding: 8px 20px;
      border-radius: 25px;
      font-weight: 600;
      font-size: 1.1rem;
    }

    .badge-player {
      background: #3399ff30;
      color: #3399ff;
      border: 2px solid #3399ff;
    }

    .badge-developer {
      background: #ff66ff30;
      color: #ff66ff;
      border: 2px solid #ff66ff;
    }

    .badge-admin {
      background: #ff333330;
      color: #ff6666;
      border: 2px solid #ff3333;
    }

    .role-buttons {
      display: flex;
      flex-direction: column;
      gap: 15px;
      margin-bottom: 30px;
    }

    .role-btn {
      padding: 15px 25px;
      border: 2px solid;
      border-radius: 10px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 600;
      text-decoration: none;
      text-align: center;
      transition: 0.3s;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .role-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 20px rgba(255, 255, 255, 0.2);
    }

    .btn-player {
      background: #3399ff20;
      color: #3399ff;
      border-color: #3399ff;
    }

    .btn-player:hover {
      background: #3399ff40;
    }

    .btn-developer {
      background: #ff66ff20;
      color: #ff66ff;
      border-color: #ff66ff;
    }

    .btn-developer:hover {
      background: #ff66ff40;
    }

    .btn-admin {
      background: #ff333320;
      color: #ff6666;
      border-color: #ff3333;
    }

    .btn-admin:hover {
      background: #ff333340;
    }

    .btn-feedback {
      width: 100%;
      padding: 15px;
      background: #1aff87;
      color: #000;
      border: none;
      border-radius: 10px;
      font-size: 1.1rem;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      display: block;
      text-align: center;
      transition: 0.3s;
      text-transform: uppercase;
    }

    .btn-feedback:hover {
      background: #11cc66;
      transform: translateY(-3px);
      box-shadow: 0 5px 20px #1aff8744;
    }

    .icon {
      font-size: 1.5rem;
    }

    .description {
      color: #aaa;
      font-size: 0.85rem;
      margin-top: 5px;
    }

    .info-box {
      background: #111;
      border-left: 4px solid #1aff87;
      padding: 15px;
      margin-top: 30px;
      border-radius: 5px;
    }

    .info-box h4 {
      color: #1aff87;
      margin-bottom: 10px;
      font-size: 0.95rem;
    }

    .info-box ul {
      margin-left: 20px;
      color: #bbb;
      font-size: 0.85rem;
      line-height: 1.8;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>🎮 Role Switcher</h1>
    <p class="subtitle">Test different user perspectives</p>

    <div class="current-role">
      <h3>Current Role</h3>
      <span class="role-badge badge-<?php echo $current_role; ?>">
        <?php 
        $icons = ['player' => '👤', 'developer' => '👨‍💻', 'admin' => '👑'];
        echo $icons[$current_role] . ' ' . strtoupper($current_role); 
        ?>
      </span>
    </div>

    <div class="role-buttons">
      <a href="?role=player" class="role-btn btn-player">
        <div>
          <div><span class="icon">👤</span> <strong>Player</strong></div>
          <div class="description">Submit feedback and view responses</div>
        </div>
        <div>➜</div>
      </a>

      <a href="?role=developer" class="role-btn btn-developer">
        <div>
          <div><span class="icon">👨‍💻</span> <strong>Developer</strong></div>
          <div class="description">Manage feedback and update status</div>
        </div>
        <div>➜</div>
      </a>

      <a href="?role=admin" class="role-btn btn-admin">
        <div>
          <div><span class="icon">👑</span> <strong>Admin</strong></div>
          <div class="description">Full access + delete permissions</div>
        </div>
        <div>➜</div>
      </a>
    </div>

    <a href="feedback.php" class="btn-feedback">
      🚀 Go to Feedback Page
    </a>

    <div class="info-box">
      <h4>💡 Role Capabilities:</h4>
      <ul>
        <li><strong>Player:</strong> Submit feedback, view status, reply</li>
        <li><strong>Developer:</strong> All player features + update status</li>
        <li><strong>Admin:</strong> All features + delete feedback</li>
      </ul>
    </div>
  </div>
</body>
</html>
