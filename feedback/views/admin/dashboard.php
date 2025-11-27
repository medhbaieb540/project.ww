<?php

session_start();
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../feedback/feedback.php');
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['role'] = 'admin';
    $_SESSION['username'] = 'AdminUser';
}

$db = Config::getConnexion();

try {
    $sql_total = "SELECT COUNT(*) as total FROM feedback";
    $query_total = $db->prepare($sql_total);
    $query_total->execute();
    $total_feedback = $query_total->fetch()['total'];
    
    $sql_types = "SELECT type, COUNT(*) as count FROM feedback GROUP BY type";
    $query_types = $db->prepare($sql_types);
    $query_types->execute();
    $types_data = $query_types->fetchAll();
    
    $reports_count = 0;
    $feedback_count = 0;
    foreach ($types_data as $type) {
        if ($type['type'] === 'report') $reports_count = $type['count'];
        if ($type['type'] === 'feedback') $feedback_count = $type['count'];
    }
    
    $sql_status = "SELECT status, COUNT(*) as count FROM feedback WHERE type = 'report' GROUP BY status";
    $query_status = $db->prepare($sql_status);
    $query_status->execute();
    $status_data = $query_status->fetchAll();
    
    $pending_count = 0;
    $reviewed_count = 0;
    $fixed_count = 0;
    foreach ($status_data as $status) {
        if ($status['status'] === 'pending') $pending_count = $status['count'];
        if ($status['status'] === 'reviewed') $reviewed_count = $status['count'];
        if ($status['status'] === 'fixed') $fixed_count = $status['count'];
    }
    
    $sql_replies = "SELECT COUNT(*) as total FROM replies";
    $query_replies = $db->prepare($sql_replies);
    $query_replies->execute();
    $total_replies = $query_replies->fetch()['total'];
    
    $sql_top_games = "SELECT game, COUNT(*) as count FROM feedback GROUP BY game ORDER BY count DESC LIMIT 5";
    $query_top_games = $db->prepare($sql_top_games);
    $query_top_games->execute();
    $top_games = $query_top_games->fetchAll();
    
    $sql_recent = "SELECT f.*, 
                   (SELECT COUNT(*) FROM replies WHERE feedback_id = f.id) as reply_count
                   FROM feedback f 
                   ORDER BY f.date DESC LIMIT 10";
    $query_recent = $db->prepare($sql_recent);
    $query_recent->execute();
    $recent_activity = $query_recent->fetchAll();
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $total_feedback = 0;
    $reports_count = 0;
    $feedback_count = 0;
    $pending_count = 0;
    $reviewed_count = 0;
    $fixed_count = 0;
    $total_replies = 0;
    $top_games = [];
    $recent_activity = [];
}

function time_ago($datetime) {
    $time_ago = time() - strtotime($datetime);
    if ($time_ago < 3600) return floor($time_ago / 60) . ' min ago';
    elseif ($time_ago < 86400) return floor($time_ago / 3600) . ' hr ago';
    elseif ($time_ago < 604800) return floor($time_ago / 86400) . ' days ago';
    else return floor($time_ago / 604800) . ' weeks ago';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | GameBridge</title>
  <link rel="stylesheet" href="../../models/css/backoffice-style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
  <div class="sidebar">
    <div class="sidebar-header">
      <h2>🎮 GameBridge</h2>
      <p>Admin Panel</p>
    </div>
    
    <nav class="sidebar-nav">
      <a href="dashboard.php" class="nav-item active">
        <span class="nav-icon">📊</span>
        <span>Dashboard</span>
      </a>
      <a href="manage-feedback.php" class="nav-item">
        <span class="nav-icon">💬</span>
        <span>Manage Feedback</span>
      </a>
      <a href="reports.php" class="nav-item">
        <span class="nav-icon">🐛</span>
        <span>Reports</span>
      </a>
      <a href="analytics.php" class="nav-item">
        <span class="nav-icon">📈</span>
        <span>Analytics</span>
      </a>
      <a href="../feedback/feedback.php" class="nav-item" onclick="setAdminRole()">
        <span class="nav-icon">👁️</span>
        <span>View as User</span>
      </a>
    </nav>
    
    <div class="sidebar-footer">
      <div class="user-info">
        <span class="user-avatar">👤</span>
        <div>
          <p class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
          <p class="user-role">Administrator</p>
        </div>
      </div>
    </div>
  </div>

  <div class="main-content">
    <header class="content-header">
      <div>
        <h1>Dashboard Overview</h1>
        <p class="subtitle">Welcome back! Here's what's happening with GameBridge feedback.</p>
      </div>
      <div class="header-actions">
        <span class="current-time">⏰ <?php echo date('l, F j, Y - g:i A'); ?></span>
      </div>
    </header>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon" style="background: #1aff8730;">💬</div>
        <div class="stat-content">
          <h3><?php echo $total_feedback; ?></h3>
          <p>Total Feedback</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon" style="background: #3399ff30;">💭</div>
        <div class="stat-content">
          <h3><?php echo $feedback_count; ?></h3>
          <p>General Feedback</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon" style="background: #ff333330;">🐛</div>
        <div class="stat-content">
          <h3><?php echo $reports_count; ?></h3>
          <p>Bug Reports</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon" style="background: #ffcc0030;">⏳</div>
        <div class="stat-content">
          <h3><?php echo $pending_count; ?></h3>
          <p>Pending Reports</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon" style="background: #ff66ff30;">👁️</div>
        <div class="stat-content">
          <h3><?php echo $reviewed_count; ?></h3>
          <p>Reviewed</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon" style="background: #1aff8730;">✅</div>
        <div class="stat-content">
          <h3><?php echo $fixed_count; ?></h3>
          <p>Fixed</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon" style="background: #66ccff30;">💬</div>
        <div class="stat-content">
          <h3><?php echo $total_replies; ?></h3>
          <p>Total Replies</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon" style="background: #ff99cc30;">🔥</div>
        <div class="stat-content">
          <h3><?php echo count($top_games); ?></h3>
          <p>Active Games</p>
        </div>
      </div>
    </div>

    <div class="dashboard-grid">
      <div class="dashboard-card">
        <div class="card-header">
          <h2>🏆 Top Games by Feedback</h2>
          <span class="badge">Last 30 days</span>
        </div>
        <div class="card-body">
          <?php if (empty($top_games)): ?>
          <p class="empty-state">No games data yet</p>
          <?php else: ?>
          <div class="top-games-list">
            <?php foreach ($top_games as $index => $game): ?>
            <div class="top-game-item">
              <span class="game-rank">#<?php echo $index + 1; ?></span>
              <div class="game-info">
                <strong><?php echo htmlspecialchars($game['game']); ?></strong>
                <small><?php echo $game['count']; ?> feedback items</small>
              </div>
              <div class="game-bar">
                <div class="game-bar-fill" style="width: <?php echo ($game['count'] / $top_games[0]['count']) * 100; ?>%;"></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="dashboard-card">
        <div class="card-header">
          <h2>🕐 Recent Activity</h2>
          <a href="manage-feedback.php" class="view-all-link">View All →</a>
        </div>
        <div class="card-body">
          <?php if (empty($recent_activity)): ?>
          <p class="empty-state">No recent activity</p>
          <?php else: ?>
          <div class="activity-list">
            <?php foreach ($recent_activity as $item): ?>
            <div class="activity-item">
              <div class="activity-icon <?php echo $item['type']; ?>">
                <?php echo $item['type'] === 'report' ? '🐛' : '💭'; ?>
              </div>
              <div class="activity-content">
                <strong><?php echo htmlspecialchars($item['game']); ?></strong>
                <p><?php echo mb_substr(htmlspecialchars($item['message']), 0, 60); ?>...</p>
                <small>by @<?php echo htmlspecialchars($item['author']); ?> • <?php echo time_ago($item['date']); ?></small>
              </div>
              <?php if ($item['type'] === 'report'): ?>
              <span class="status-badge status-<?php echo $item['status']; ?>">
                <?php echo ucfirst($item['status']); ?>
              </span>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="dashboard-card full-width">
      <div class="card-header">
        <h2>⚡ Quick Actions</h2>
      </div>
      <div class="card-body">
        <div class="quick-actions">
          <a href="manage-feedback.php" class="action-btn">
            <span class="action-icon">💬</span>
            <div>
              <strong>Manage All Feedback</strong>
              <small>View and manage all feedback items</small>
            </div>
          </a>
          
          <a href="reports.php?status=pending" class="action-btn">
            <span class="action-icon">⏳</span>
            <div>
              <strong>Review Pending Reports</strong>
              <small><?php echo $pending_count; ?> reports waiting</small>
            </div>
          </a>
          
          <a href="../feedback/feedback.php" class="action-btn">
            <span class="action-icon">➕</span>
            <div>
              <strong>Add New Feedback</strong>
              <small>Submit as admin user</small>
            </div>
          </a>
          
          <a href="analytics.php" class="action-btn">
            <span class="action-icon">📈</span>
            <div>
              <strong>View Analytics</strong>
              <small>Detailed reports and insights</small>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>

  <script>
    function setAdminRole() {
      sessionStorage.setItem('viewingAsAdmin', 'true');
    }
  </script>
  <script src="../../models/js/backoffice.js"></script>
</body>
</html>