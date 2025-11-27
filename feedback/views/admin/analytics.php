<?php

session_start();
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../feedback.php');
    exit();
}

$db = Config::getConnexion();

$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');

try {
    $sql_timeline = "SELECT DATE(date) as day, COUNT(*) as count, type 
                     FROM feedback 
                     WHERE date BETWEEN :date_from AND :date_to
                     GROUP BY DATE(date), type 
                     ORDER BY day ASC";
    $query_timeline = $db->prepare($sql_timeline);
    $query_timeline->execute(['date_from' => $date_from, 'date_to' => $date_to]);
    $timeline_data = $query_timeline->fetchAll();
    
    $sql_contributors = "SELECT author, COUNT(*) as count 
                         FROM feedback 
                         WHERE date BETWEEN :date_from AND :date_to
                         GROUP BY author 
                         ORDER BY count DESC 
                         LIMIT 10";
    $query_contributors = $db->prepare($sql_contributors);
    $query_contributors->execute(['date_from' => $date_from, 'date_to' => $date_to]);
    $top_contributors = $query_contributors->fetchAll();
    
    $sql_response = "SELECT 
                     AVG(TIMESTAMPDIFF(HOUR, f.date, r.date)) as avg_hours,
                     MIN(TIMESTAMPDIFF(HOUR, f.date, r.date)) as min_hours,
                     MAX(TIMESTAMPDIFF(HOUR, f.date, r.date)) as max_hours
                     FROM feedback f
                     LEFT JOIN replies r ON f.id = r.feedback_id
                     WHERE f.type = 'report' 
                     AND r.date IS NOT NULL
                     AND f.date BETWEEN :date_from AND :date_to";
    $query_response = $db->prepare($sql_response);
    $query_response->execute(['date_from' => $date_from, 'date_to' => $date_to]);
    $response_stats = $query_response->fetch();
    
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
      <a href="dashboard.php" class="nav-item">
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
      <a href="analytics.php" class="nav-item active">
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
        <h1>Analytics & Reports</h1>
        <p class="subtitle">Detailed insights and performance metrics</p>
      </div>
    </header>

    <div class="dashboard-card">
      <div class="card-header">
        <h2>📅 Date Range</h2>
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
        <h2>🏆 Top Contributors</h2>
      </div>
      <div class="card-body">
        <?php if (empty($top_contributors)): ?>
        <p class="empty-state">No contributor data for selected period</p>
        <?php else: ?>
        <div class="top-games-list">
          <?php foreach ($top_contributors as $index => $contributor): ?>
          <div class="top-game-item">
            <span class="game-rank">#<?php echo $index + 1; ?></span>
            <div class="game-info">
              <strong>@<?php echo htmlspecialchars($contributor['author']); ?></strong>
              <small><?php echo $contributor['count']; ?> contributions</small>
            </div>
            <div class="game-bar">
              <div class="game-bar-fill" style="width: <?php echo ($contributor['count'] / $top_contributors[0]['count']) * 100; ?>%;"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="dashboard-card full-width">
      <div class="card-header">
        <h2>📈 Activity Timeline</h2>
      </div>
      <div class="card-body">
        <?php if (empty($timeline_data)): ?>
        <p class="empty-state">No activity data for selected period</p>
        <?php else: ?>
        <div style="overflow-x: auto;">
          <table style="width: 100%; color: #fff; border-collapse: collapse;">
            <thead>
              <tr style="border-bottom: 2px solid var(--accent);">
                <th style="padding: 12px; text-align: left;">Date</th>
                <th style="padding: 12px; text-align: center;">Type</th>
                <th style="padding: 12px; text-align: right;">Count</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($timeline_data as $item): ?>
              <tr style="border-bottom: 1px solid #333;">
                <td style="padding: 10px;"><?php echo date('M j, Y', strtotime($item['day'])); ?></td>
                <td style="padding: 10px; text-align: center;">
                  <span class="feedback-type type-<?php echo $item['type']; ?>">
                    <?php echo $item['type'] === 'report' ? '🐛 Report' : '💭 Feedback'; ?>
                  </span>
                </td>
                <td style="padding: 10px; text-align: right; font-weight: 600;">
                  <?php echo $item['count']; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
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
