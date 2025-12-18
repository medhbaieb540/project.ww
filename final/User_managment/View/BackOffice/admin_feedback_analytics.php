<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../FrontOffice/feedback.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../Controller/FeedbackController.php';

$controller = new FeedbackController($pdo);

$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');

try {
    $timelineStmt = $pdo->prepare("SELECT DATE(date) as day, COUNT(*) as count, type 
                     FROM feedback 
                     WHERE date BETWEEN :date_from AND :date_to
                     GROUP BY DATE(date), type 
                     ORDER BY day ASC");
    $timelineStmt->execute(['date_from' => $date_from, 'date_to' => $date_to]);
    $timeline_data = $timelineStmt->fetchAll();
    
    $contributorsStmt = $pdo->prepare("SELECT author, COUNT(*) as count 
                         FROM feedback 
                         WHERE date BETWEEN :date_from AND :date_to
                         GROUP BY author 
                         ORDER BY count DESC 
                         LIMIT 10");
    $contributorsStmt->execute(['date_from' => $date_from, 'date_to' => $date_to]);
    $top_contributors = $contributorsStmt->fetchAll();
    
    $responseStmt = $pdo->prepare("SELECT 
                     AVG(TIMESTAMPDIFF(HOUR, f.date, r.date)) as avg_hours,
                     MIN(TIMESTAMPDIFF(HOUR, f.date, r.date)) as min_hours,
                     MAX(TIMESTAMPDIFF(HOUR, f.date, r.date)) as max_hours
                     FROM feedback f
                     LEFT JOIN replies r ON f.id = r.feedback_id
                     WHERE f.type = 'report' 
                     AND r.date IS NOT NULL
                     AND f.date BETWEEN :date_from AND :date_to");
    $responseStmt->execute(['date_from' => $date_from, 'date_to' => $date_to]);
    $response_stats = $responseStmt->fetch();
    
} catch (Exception $e) {
    error_log("Analytics error: " . $e->getMessage());
    $timeline_data = [];
    $top_contributors = [];
    $response_stats = ['avg_hours' => 0, 'min_hours' => 0, 'max_hours' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics | GameBridge Admin</title>
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
    <a href="event/event.php" class="nav-link">Events</a>

    <a href="#" class="nav-link" id="feedbackToggle">Feedback ▾</a>
    <div id="feedbackSubmenu" style="display:none; padding-left:12px;">
      <a href="admin_feedback.php" class="nav-link" style="padding:8px 0;">Dashboard</a>
      <a href="admin_feedback_manage.php" class="nav-link" style="padding:8px 0;">Manage</a>
      <a href="admin_feedback_analytics.php" class="nav-link active" style="padding:8px 0;">Analytics</a>
    </div>

    <a href="../FrontOffice/logout.php"
      onclick="return confirm('Are you sure you want to logout?');"
      style="background:#ff4d4d; color:#000; font-weight:700;"
      class="nav-link">
      Logout
    </a>
  </div>

  <div class="main-content">
    <header class="content-header">
      <div>
        <h1>Analytics & Reports</h1>
        <p class="subtitle">Detailed insights and performance metrics</p>
      </div>
    </header>

    <div class="dashboard-card">
      <div class="card-header">
        <h2>Date Range</h2>
      </div>
      <div class="card-body">
        <form method="GET" action="" class="filter-form">
          <div class="filter-group">
            <label>From</label>
            <input type="date" name="date_from" value="<?php echo $date_from; ?>" required>
          </div>
          
          <div class="filter-group">
            <label>To</label>
            <input type="date" name="date_to" value="<?php echo $date_to; ?>" required>
          </div>
          
          <div class="filter-actions">
            <button type="submit" class="btn btn-primary">Apply</button>
          </div>
        </form>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon" style="background: #3399ff30;">⚡</div>
        <div class="stat-content">
          <h3><?php echo round($response_stats['avg_hours'] ?? 0, 1); ?>h</h3>
          <p>Avg Response Time</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon" style="background: #1aff8730;">🚀</div>
        <div class="stat-content">
          <h3><?php echo round($response_stats['min_hours'] ?? 0, 1); ?>h</h3>
          <p>Fastest Response</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon" style="background: #ff333330;">🐌</div>
        <div class="stat-content">
          <h3><?php echo round($response_stats['max_hours'] ?? 0, 1); ?>h</h3>
          <p>Slowest Response</p>
        </div>
      </div>
    </div>

    <div class="dashboard-card">
      <div class="card-header">
        <h2>Top Contributors</h2>
      </div>
      <div class="card-body">
        <?php if (empty($top_contributors)): ?>
        <p class="empty-state">No contributor data for selected period</p>
        <?php else: ?>
        <div class="top-list">
          <?php foreach ($top_contributors as $index => $contributor): ?>
          <div class="top-item">
            <span class="rank">#<?php echo $index + 1; ?></span>
            <div style="flex:1;">
              <strong>@<?php echo htmlspecialchars($contributor['author']); ?></strong>
              <small><?php echo $contributor['count']; ?> contributions</small>
              <div class="progress"><div class="progress-fill" style="width: <?php echo ($contributor['count'] / $top_contributors[0]['count']) * 100; ?>%"></div></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="dashboard-card full-width">
      <div class="card-header">
        <h2>Activity Timeline</h2>
      </div>
      <div class="card-body">
        <?php if (empty($timeline_data)): ?>
        <p class="empty-state">No activity data for selected period</p>
        <?php else: ?>
        <div style="overflow-x: auto;">
          <table style="width: 100%; color: #fff; border-collapse: collapse;">
            <thead>
              <tr style="border-bottom: 2px solid var(--accent);">
                <th style="text-align:left; padding:10px;">Date</th>
                <th style="text-align:left; padding:10px;">Type</th>
                <th style="text-align:left; padding:10px;">Count</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($timeline_data as $row): ?>
              <tr style="border-bottom: 1px solid #333;">
                <td style="padding:10px;"><?php echo htmlspecialchars($row['day']); ?></td>
                <td style="padding:10px;"><?php echo htmlspecialchars(ucfirst($row['type'])); ?></td>
                <td style="padding:10px;"><?php echo (int)$row['count']; ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <script src="../../public/js/backoffice.js"></script>
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
