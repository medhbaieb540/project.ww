<?php
// View/FrontOffice/profile.php
require_once __DIR__ . '/../../Controller/ProfileAction.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>GameBridge | My Profile</title>
  <link rel="stylesheet" href="../../public/css/stylePro.css" />
  <link rel="stylesheet" href="../../public/css/profile_inline.css" />
</head>
<body>
    <header>
    <div class="logo-container">
      <img src="../../public/images/logo.png" alt="Logo" class="logo">
    </div>
    <nav>
      <a href="../index.html">Home</a>
      <a href="list.php">Games</a>
      <a href="tournaments.php">Tournaments</a>
      <a href="community.php">Community</a>
      <a href="event.php">Events</a>
      <a href="feedback.php">Feedback</a>
      <a href="users.html" class="active">My Profile</a>
      <a href="logout.php"
         onclick="return confirm('Are you sure you want to logout?');"
         class="logout-btn">Logout</a>
    </nav>
  </header>

  <section>
    <!-- ===== Profile Header ===== -->
    <div class="profile-header">
      <img src="../../public/images/avatar.jpg" alt="User Avatar" class="avatar">
      <div class="profile-info">
        <h1>@<?= htmlspecialchars($user['username']) ?></h1>

        <?php if ($role === 'developer'): ?>
          <p>Indie Game Developer | Joined March 2025</p>
          <span class="role-badge">Developer</span>
        <?php elseif ($role === 'player'): ?>
          <p>Passionate Gamer | Joined March 2025</p>
          <span class="role-badge">Player</span>
        <?php else: ?>
          <p>GameBridge Member</p>
          <span class="role-badge"><?= htmlspecialchars(ucfirst($role)) ?></span>
        <?php endif; ?>

        <div class="xp-bar">
          <div class="xp-fill"></div>
        </div>
        <p style="font-size:0.8rem; color:#888;">Level 7 • XP: 650/1000</p>
      </div>
    </div>

    <!-- ===== Uploaded Games ===== -->
    <div class="dashboard-section">
      <h2>🎮 Uploaded Games</h2>
      <div class="games-grid">
        <div class="game-card">
          <img src="../../public/images/game1.jpg" alt="Game Cover">
          <h3>Monster Dream</h3>
          <p>Downloads: 2.3K | Rating: 4.8⭐</p>
        </div>
        <div class="game-card">
          <img src="../../public/images/game2.jpg" alt="Game Cover">
          <h3>Memories & Quest</h3>
          <p>Downloads: 1.1K | Rating: 4.5⭐</p>
        </div>
        <div class="game-card">
          <img src="../../public/images/game3.jpg" alt="Game Cover">
          <h3>Dead Curse</h3>
          <p>Downloads: 3.9K | Rating: 4.9⭐</p>
        </div>
      </div>
    </div>

    <!-- ===== Tournaments ===== -->
    <div class="dashboard-section">
      <h2>🏆 Tournaments Joined</h2>
      <div class="list">
        <div class="list-item"><p>Cyber Clash 2025</p><span>Top 10%</span></div>
        <div class="list-item"><p>Neon Rush Cup</p><span>Champion</span></div>
        <div class="list-item"><p>Retro Revival Jam</p><span>Participant</span></div>
      </div>
    </div>

    <!-- ===== Feedback ===== -->
    <div class="dashboard-section">
      <h2>🐞 Recent Feedback</h2>
      <div class="list">
        <div class="list-item"><p>Reported bug in “Monster Dream” – collision issue.</p><span>Fixed</span></div>
        <div class="list-item"><p>Suggested new feature: cloud save sync.</p><span>Under Review</span></div>
      </div>
    </div>

    <!-- ===== Achievements ===== -->
    <div class="dashboard-section">
      <h2>⭐ Achievements</h2>
      <div class="achievements">
        <div class="badge"><i>🏅</i>Top Dev</div>
        <div class="badge"><i>🔥</i>Active User</div>
        <div class="badge"><i>💬</i>Community Hero</div>
        <div class="badge"><i>🎯</i>Precision Coder</div>
      </div>
    </div>
  </section>

  <footer>© 2025 GameBridge • Developed by Team UnityForge</footer>
</body>
</html>
