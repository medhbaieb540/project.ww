<?php
session_start();

require_once '../../controller/UserController.php';
require_once '../../config.php';


$userC = new UserController();
$users = $userC->listUsers();   // جلب المستخدمين من الداتابيس

$currentRole = $_SESSION['user_role'] ?? null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>GameBridge | All Users</title>
  <link rel="stylesheet" href="../FrontOffice/styleAdmin.css" />
  <style>
    /* ===== بنفس تصميمك ===== */
    body {
      background: #0c0c0c;
      color: var(--text);
      font-family: 'Poppins', sans-serif;
    }
    section { padding: 40px 10%; }
    h2 { font-family: 'Orbitron', sans-serif; color:var(--accent); }

    .games-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 30px;
    }

    .game-card {
      background: var(--bg-card);
      border: 1px solid #1aff8722;
      border-radius: 12px;
      padding: 20px;
      transition: 0.3s;
    }

    .game-card:hover {
      border-color: var(--accent);
      box-shadow: 0 0 20px #1aff8733;
      transform: translateY(-5px);
    }

    .card-btn {
      padding: 6px 10px;
      border-radius: 6px;
      font-size: 0.9rem;
      margin-right: 10px;
      cursor: pointer;
    }

    .update-btn {
      border: 2px solid var(--accent);
      color: var(--accent);
      background: transparent;
    }

    .delete-btn {
      border: 2px solid #ff4d4d;
      color: #ff4d4d;
      background: transparent;
    }
  </style>
</head>
<body>

<header>
  <div class="logo-container">
    <img src="../FrontOffice/logo.png" alt="Logo" />
  </div>
  <nav>
    <a href="../index.html">Home</a>
    <a href="games.html">Games</a>
    <a href="tournaments.html">Tournaments</a>
    <a href="community.html">Community</a>
    <a href="my_profile.html">My Profile</a>
    <a href="feedback.html">Feedback</a>
    <a href="rewards.html">Rewards</a>
  </nav>
</header>

<section>
  <h2>All Users</h2>
  <br><br>

  <div class="games-grid">

  <?php foreach ($users as $u): ?>
    <?php if($u['user_role']=='super_admin') continue; ?>  <!-- hide him -->
    <div class="game-card">
      <h3>@<?= htmlspecialchars($u['username']) ?></h3> 
      <p>Email: <?= htmlspecialchars($u['email']) ?></p>
      <p>Role: <?= htmlspecialchars($u['user_role']) ?></p>
      <p>
        Status: 
        <?php if (!empty($u['is_banned']) && (int)$u['is_banned'] === 1): ?>
          <span style="color:#ff4d4d;">Banned</span>
        <?php else: ?>
          <span style="color:#1aff87;">Active</span>
        <?php endif; ?>
      </p>

      <div style="margin-top:10px;">
        <a href="update.php?id=<?= $u['id'] ?>">
          <button class="card-btn update-btn">Edit</button>
        </a>

        <?php if (empty($u['is_banned']) || (int)$u['is_banned'] === 0): ?>
          <!-- لو مش محظور نعرض زر Ban -->
          <a href="ban.php?id=<?= $u['id'] ?>"
             onclick="return confirm('Ban this user?');">
            <button class="card-btn delete-btn">Ban</button>
          </a>
        <?php else: ?>
          <!-- لو محظور نعرض Unban + Delete -->
          <a href="unban.php?id=<?= $u['id'] ?>"
             onclick="return confirm('Unban this user?');">
            <button class="card-btn update-btn">Unban</button>
          </a>

          <a href="delete.php?id=<?= $u['id'] ?>"
             onclick="return confirm('User is banned. Delete permanently?');">
            <button class="card-btn delete-btn">Delete</button>
          </a>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

</div>

</section>

<footer>© 2025 GameBridge • Developed by Team UnityForge</footer>

</body>
</html>
