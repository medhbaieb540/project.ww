<?php
session_start();

/*
  Simple manual "fake login" system.
  Each role uses a different user_id and username.
*/

if (isset($_GET['role'])) {

    if ($_GET['role'] === 'admin') {
        $_SESSION['user_id']  = 1;
        $_SESSION['username'] = 'AdminUser';
        $_SESSION['role']     = 'admin';
    }

    if ($_GET['role'] === 'player') {
        $_SESSION['user_id']  = 2;
        $_SESSION['username'] = 'PlayerUser';
        $_SESSION['role']     = 'player';
    }

    if ($_GET['role'] === 'developer') {
        $_SESSION['user_id']  = 3;
        $_SESSION['username'] = 'DeveloperUser';
        $_SESSION['role']     = 'developer';
    }

    header("Location: community.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Switch User — GameBridge</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body { background:#0c0c0c; color:#fff; font-family:Arial; padding:40px; }
  .card { background:#111; border:1px solid #1aff8720; padding:20px; border-radius:8px; width:350px; margin:auto; }
  h1 { color:#1aff87; margin-bottom:10px; text-align:center; }
  a.btn { display:block; padding:12px; margin:10px 0; 
          background:#1aff87; color:#000; text-align:center;
          border-radius:8px; text-decoration:none; font-weight:bold; }
  a.back { color:#1aff87; text-decoration:none; display:block; margin-top:12px; text-align:center; }
</style>
</head>

<body>
<div class="card">
  <h1>Switch User</h1>

  <a class="btn" href="switchuser.php?role=player">Switch to Player</a>
  <a class="btn" href="switchuser.php?role=developer">Switch to Developer</a>
  <a class="btn" href="switchuser.php?role=admin">Switch to Admin</a>

  <a class="back" href="community.php">⬅ Back to Community</a>
</div>
</body>
</html>
