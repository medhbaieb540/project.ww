<?php
session_set_cookie_params(['path' => '/FINAL/', 'httponly' => true]);
session_start();

$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
if ($userId === null) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../Controller/CommunityController.php';

$controller = new CommunityController($pdo);
$username = $_SESSION['username'] ?? 'Player';
$userRole = $_SESSION['user_role'] ?? ($_SESSION['role'] ?? 'player');

$posts = $controller->getArchivedPosts((int) $userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GameBridge | Archived Posts</title>
  <link rel="stylesheet" href="../../public/css/stylecommunity.css">
</head>
<body>
<header>
  <div class="logo-container">
    <h1>GameBridge</h1>
  </div>
  <nav>
    <a href="index.php">Home</a>
    <a href="games.php">Games</a>
    <a href="tournaments.php">Tournaments</a>
    <a href="community.php">Community</a>
    <a href="event.php">Events</a>
    <a href="profile.php">My Profile</a>
    <a href="feedback.php">Feedback</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<section>
  <h2>Archived Posts</h2>
  <p class="description">Your archived posts stay here until you bring them back.</p>
  <a class="btn btn-secondary" href="community.php" style="margin-bottom:16px; display:inline-block;">← Back to feed</a>

  <div class="games-community-grid">
    <?php foreach ($posts as $post): ?>
      <div class="game-card" data-post-id="<?php echo (int) $post['id']; ?>">
        <div style="display:flex; justify-content:space-between; margin-bottom:8px; color:#aaa; font-size:0.9rem;">
          <span>@<?php echo htmlspecialchars($post['username'] ?? 'user'); ?> • <?php echo date('M j, Y H:i', strtotime($post['created_at'])); ?></span>
          <span class="badge" style="background:#222; border:1px solid var(--accent); padding:4px 8px; border-radius:6px; color:var(--accent);">Archived</span>
        </div>

        <?php if (!empty($post['image'])): ?>
          <img src="../../public/images/uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Post image">
        <?php endif; ?>

        <p style="margin:10px 0 12px; color:#ddd; white-space:pre-wrap;"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>

        <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:12px;">
          <button class="btn-secondary" style="padding:8px 12px;" data-action="unarchive" data-post-id="<?php echo (int) $post['id']; ?>">Unarchive</button>
          <button class="btn-secondary" style="padding:8px 12px; background:#ff3333; color:#fff;" data-action="delete" data-post-id="<?php echo (int) $post['id']; ?>">Delete</button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<script>
  document.querySelectorAll('[data-action="unarchive"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const postId = btn.getAttribute('data-post-id');
      fetch('../../Controller/unarchive_post_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${encodeURIComponent(postId)}`
      }).then(r => r.json()).then(data => {
        if (data.success) {
          window.location.reload();
        }
      });
    });
  });

  document.querySelectorAll('[data-action="delete"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const postId = btn.getAttribute('data-post-id');
      if (!confirm('Delete this archived post?')) return;
      fetch('../../Controller/delete_post_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${encodeURIComponent(postId)}`
      }).then(r => r.json()).then(data => {
        if (data.success) {
          window.location.reload();
        }
      });
    });
  });
</script>
</body>
</html>
