<?php
// View/FrontOffice/profile.php
require_once __DIR__ . '/../../Controller/ProfileAction.php';
require_once __DIR__ . '/../../config/db.php';

// Fetch user data from database
$user_id = $_SESSION['user_id'] ?? null;
$user = [];
$games = [];
$tournaments = [];
$feedback = [];

if ($user_id) {
  try {
    // Get user info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Get user's games
    $stmt = $pdo->prepare("SELECT * FROM games WHERE developer_id = ? LIMIT 5");
    $stmt->execute([$user_id]);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    // Get user's tournament participation (handle table name variations)
    try {
      $stmt = $pdo->prepare("SELECT t.* FROM tournaments t 
                            JOIN participations p ON t.id = p.tournament_id 
                            WHERE p.user_id = ? LIMIT 5");
      $stmt->execute([$user_id]);
      $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
      // Try alternative table name
      try {
        $stmt = $pdo->prepare("SELECT t.* FROM tournaments t 
                              JOIN participation p ON t.id = p.tournament_id 
                              WHERE p.user_id = ? LIMIT 5");
        $stmt->execute([$user_id]);
        $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
      } catch (PDOException $e2) {
        $tournaments = [];
      }
    }
    
    // Get user's feedback
    try {
      $stmt = $pdo->prepare("SELECT * FROM feedback WHERE user_id = ? LIMIT 5");
      $stmt->execute([$user_id]);
      $feedback = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
      // Try alternative table name
      try {
        $stmt = $pdo->prepare("SELECT * FROM feedback WHERE user_id = ? LIMIT 5");
        $stmt->execute([$user_id]);
        $feedback = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
      } catch (PDOException $e2) {
        $feedback = [];
      }
    }
  } catch (PDOException $e) {
    error_log("Profile error: " . $e->getMessage());
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>GameBridge | My Profile</title>
  <link rel="stylesheet" href="../../public/css/frontoffice-header.css" />
  <link rel="stylesheet" href="../../public/css/stylePro.css" />
  <link rel="stylesheet" href="../../public/css/profile_inline.css" />
</head>
<body>
    <header>
    <div class="logo-container">
      <img src="../../public/images/logo.png" alt="Logo" class="logo">
    </div>
    <nav>
      <a href="index.php">Home</a>
      <a href="tournaments.php">Tournaments</a>
      <a href="community.php">Community</a>
      <a href="list.php">Games</a>
      <a href="event.php">Events</a>
      <a href="feedback.php">Feedback</a>
      <a href="profile.php" class="active">My Profile</a>
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
        <h1>@<?= htmlspecialchars($user['username'] ?? 'Player') ?></h1>

        <?php 
        $role = $user['user_role'] ?? 'player';
        $created_at = $user['created_at'] ?? date('Y-m-d');
        ?>
        
        <?php if ($role === 'developer'): ?>
          <p>Indie Game Developer | Joined <?= date('F Y', strtotime($created_at)) ?></p>
          <span class="role-badge">Developer</span>
        <?php elseif ($role === 'player'): ?>
          <p>Passionate Gamer | Joined <?= date('F Y', strtotime($created_at)) ?></p>
          <span class="role-badge">Player</span>
        <?php else: ?>
          <p>GameBridge Member</p>
          <span class="role-badge"><?= htmlspecialchars(ucfirst($role)) ?></span>
        <?php endif; ?>

        <div class="xp-bar">
          <div class="xp-fill" style="width: <?= ($user['xp'] ?? 0) % 100 ?>%;"></div>
        </div>
        <p style="font-size:0.8rem; color:#888;">Level <?= intdiv($user['xp'] ?? 0, 100) + 1 ?> • XP: <?= ($user['xp'] ?? 0) % 100 ?>/100</p>
      </div>
    </div>

    <!-- ===== Games Section ===== -->
    <div class="dashboard-section">
      <?php 
      $role = $user['user_role'] ?? 'player';
      $gameTitle = ($role === 'developer') ? 'My Games' : 'Games Played';
      $gameEmpty = ($role === 'developer') ? 'No games created yet' : 'No games played yet';
      ?>
      <h2>🎮 <?= $gameTitle ?> (<?= count($games) ?>)</h2>
      <div class="games-grid">
        <?php if (!empty($games)): ?>
          <?php foreach ($games as $game): ?>
            <div class="game-card">
              <?php if (!empty($game['image_path'])): ?>
                <img src="../../<?= htmlspecialchars($game['image_path']) ?>" alt="<?= htmlspecialchars($game['title']) ?>" />
              <?php else: ?>
                <img src="../../assets/images/game1.jpg" alt="No Image" />
              <?php endif; ?>
              <h3><?= htmlspecialchars($game['title'] ?? 'Untitled') ?></h3>
              <p class="game-info">
                Category: <b><?= htmlspecialchars($game['category_id'] ?? 'N/A') ?></b> • 
                ★ <?= number_format($game['average_rating'] ?? 0, 1) ?>
              </p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="color: #999; grid-column: 1/-1; text-align: center; padding: 20px;"><?= $gameEmpty ?></p>
        <?php endif; ?>
      </div>
    </div>

    <!-- ===== Tournaments ===== -->
    <div class="dashboard-section">
      <h2>🏆 Tournaments Joined (<?= count($tournaments) ?>)</h2>
      <div class="list">
        <?php if (!empty($tournaments)): ?>
          <?php foreach ($tournaments as $tournament): ?>
            <div class="list-item">
              <p><?= htmlspecialchars($tournament['name'] ?? 'Unknown Tournament') ?></p>
              <span style="color: #1aff87;"><?= htmlspecialchars($tournament['status'] ?? 'Active') ?></span>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="list-item"><p style="color: #999;">No tournaments joined yet</p></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ===== Feedback ===== -->
    <div class="dashboard-section">
      <h2>🐞 Recent Feedback (<?= count($feedback) ?>)</h2>
      <div class="list">
        <?php if (!empty($feedback)): ?>
          <?php foreach ($feedback as $fb): ?>
            <div class="list-item">
              <p><?= htmlspecialchars(substr($fb['message'] ?? 'No content', 0, 60) . '...') ?></p>
              <span style="color: <?= ($fb['status'] ?? 'pending') === 'resolved' ? '#1aff87' : '#ffaa00' ?>;">
                <?= htmlspecialchars(ucfirst($fb['status'] ?? 'Pending')) ?>
              </span>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="list-item"><p style="color: #999;">No feedback submitted yet</p></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ===== Achievements ===== -->
    <div class="dashboard-section">
      <h2>⭐ Achievements</h2>
      <div class="achievements">
        <?php 
        $achievements = [];
        
        // Dynamic achievements based on user data
        if (count($games) >= 5) $achievements[] = ['🏅', 'Prolific Creator'];
        if (count($tournaments) >= 3) $achievements[] = ['🔥', 'Tournament Champion'];
        if (count($feedback) >= 5) $achievements[] = ['💬', 'Community Helper'];
        if ($user['xp'] ?? 0 >= 500) $achievements[] = ['🎯', 'Experience Master'];
        if ($role === 'developer') $achievements[] = ['👨‍💻', 'Developer'];
        
        if (!empty($achievements)):
          foreach ($achievements as $achievement):
            echo '<div class="badge"><i>' . $achievement[0] . '</i>' . $achievement[1] . '</div>';
          endforeach;
        else:
          echo '<p style="color: #999; grid-column: 1/-1;">Keep playing to unlock achievements!</p>';
        endif;
        ?>
      </div>
    </div>
  </section>

  <footer>© 2025 GameBridge • Developed by Team UnityForge</footer>
</body>
</html>
