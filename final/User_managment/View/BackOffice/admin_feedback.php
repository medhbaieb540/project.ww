<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../FrontOffice/feedback.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../Controller/FeedbackController.php';

$controller = new FeedbackController($pdo);
$stats = $controller->getStats();
$recent = $controller->getRecentActivity(5);
$topGames = $controller->getTopGames(5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Feedback | GameBridge</title>
  <link rel="stylesheet" href="../../public/css/styleAdmin.css">
  <link rel="stylesheet" href="../../public/css/admin_inline.css">
  <link rel="stylesheet" href="../../public/css/backoffice-style.css">
</head>
<body>
  <div class="sidebar">
    <h2>Admin</h2>

    <a href="admin.php" class="nav-link">Dashboard</a>
    <a href="admin.php?section=users" class="nav-link">Users</a>
    <a href="games_dashboard.php" class="nav-link">Games</a>
    <a href="admintour.php" class="nav-link">Tournaments</a>
    <a href="adminrewards.php" class="nav-link">Rewards</a>
    <a href="/gamebridge/final/User_managment/View/BackOffice/event/event.php" class="nav-link">Events</a>

    <a href="#" class="nav-link" id="feedbackToggle">Feedback ▾</a>
    <div id="feedbackSubmenu" style="display:none; padding-left:12px;">
      <a href="admin_feedback.php" class="nav-link active" style="padding:8px 0;">Dashboard</a>
      <a href="admin_feedback_manage.php" class="nav-link" style="padding:8px 0;">Manage</a>
      <a href="admin_feedback_analytics.php" class="nav-link" style="padding:8px 0;">Analytics</a>
    </div>

    <a href="../FrontOffice/logout.php"
      onclick="return confirm('Are you sure you want to logout?');"
      style="background:#ff4d4d; color:#000; font-weight:700;"
      class="nav-link">
      Logout
    </a>
  </div>

  <div class="main-content">
    <div class="content-header">
      <div>
        <h1>Feedback Overview</h1>
        <p class="subtitle">Monitor reports and feedback across all games.</p>
      </div>
      <div class="header-actions">
        <a class="btn btn-primary" href="admin_feedback_manage.php">Manage</a>
        <a class="btn btn-secondary" href="../../View/FrontOffice/feedback.php?from=admin_feedback">View as User</a>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon">📥</div><div class="stat-content"><h3><?php echo (int)$stats['total']; ?></h3><p>Total Items</p></div></div>
      <div class="stat-card"><div class="stat-icon">🚧</div><div class="stat-content"><h3><?php echo (int)$stats['pending']; ?></h3><p>Pending</p></div></div>
      <div class="stat-card"><div class="stat-icon">👁️</div><div class="stat-content"><h3><?php echo (int)$stats['reviewed']; ?></h3><p>Reviewed</p></div></div>
      <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-content"><h3><?php echo (int)$stats['fixed']; ?></h3><p>Fixed</p></div></div>
      <div class="stat-card"><div class="stat-icon">🐞</div><div class="stat-content"><h3><?php echo (int)$stats['reports']; ?></h3><p>Reports</p></div></div>
      <div class="stat-card"><div class="stat-icon">💬</div><div class="stat-content"><h3><?php echo (int)$stats['feedback']; ?></h3><p>Feedback</p></div></div>
    </div>

    <div class="dashboard-card">
      <div class="card-header">
        <h2>Quick Actions</h2>
      </div>
      <div class="card-body" style="display:flex; gap:14px; flex-wrap:wrap;">
        <a class="btn btn-primary" href="admin_feedback_manage.php">Manage All</a>
        <a class="btn btn-secondary" href="admin_feedback_manage.php?status=pending">Pending Reports</a>
        <a class="btn btn-secondary" href="../../View/FrontOffice/feedback.php?from=admin_feedback">Submit as User</a>
        <a class="btn btn-secondary" href="admin_feedback_analytics.php">View Analytics</a>
      </div>
    </div>

    <div class="dashboard-card">
      <div class="card-header">
        <h2>Top Games</h2>
      </div>
      <div class="card-body">
        <?php if (empty($topGames)): ?>
          <div class="empty-state">No feedback yet.</div>
        <?php else: ?>
          <div class="top-list">
            <?php foreach ($topGames as $index => $game): ?>
              <div class="top-item">
                <span class="rank">#<?php echo $index + 1; ?></span>
                <div style="flex:1;">
                  <strong><?php echo htmlspecialchars($game['game']); ?></strong>
                  <div class="progress"><div class="progress-fill" style="width: <?php echo ($game['count'] / $topGames[0]['count']) * 100; ?>%"></div></div>
                </div>
                <span class="chip"><?php echo (int)$game['count']; ?> items</span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="dashboard-card">
      <div class="card-header">
        <h2>Recent Activity</h2>
        <a class="view-all-link" href="admin_feedback_manage.php">View all →</a>
      </div>
      <div class="card-body">
        <?php if (empty($recent)): ?>
          <div class="empty-state">No recent feedback.</div>
        <?php else: ?>
          <div class="feedback-table">
            <?php foreach ($recent as $item): ?>
              <div class="feedback-row">
                <div class="feedback-row-header">
                  <div class="feedback-row-title">
                    <h3><?php echo htmlspecialchars($item['game']); ?></h3>
                    <span class="feedback-type type-<?php echo htmlspecialchars($item['type']); ?>"><?php echo ucfirst($item['type']); ?></span>
                    <span class="status-badge status-<?php echo htmlspecialchars($item['status']); ?>"><?php echo ucfirst($item['status']); ?></span>
                  </div>
                  <div class="feedback-row-meta">
                    <span>@<?php echo htmlspecialchars($item['author']); ?></span>
                    <span>•</span>
                    <span><?php echo date('M j, Y', strtotime($item['date'])); ?></span>
                    <span>•</span>
                    <span><?php echo (int)$item['reply_count']; ?> replies</span>
                  </div>
                </div>
                <div class="feedback-row-content"><?php echo nl2br(htmlspecialchars($item['message'])); ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <script src="../../public/js/feedback.js"></script>
  <script>
    (function() {
      const toggle = document.getElementById('feedbackToggle');
      const submenu = document.getElementById('feedbackSubmenu');
      if (!toggle || !submenu) return;

      toggle.addEventListener('click', function (e) {
        e.preventDefault();
        const isOpen = submenu.style.display === 'block';
        submenu.style.display = isOpen ? 'none' : 'block';
        toggle.textContent = isOpen ? 'Feedback ▾' : 'Feedback ▴';
      });

      const page = (location.pathname.split('/').pop() || '').toLowerCase();
      if (page.includes('admin_feedback')) {
        submenu.style.display = 'block';
        toggle.textContent = 'Feedback ▴';
      }
    })();
  </script>
</body>
</html>
