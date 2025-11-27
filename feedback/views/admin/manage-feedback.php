<?php

session_start();
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../feedback.php');
    exit();
}

$db = Config::getConnexion();

$filter_type = $_GET['type'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';
$filter_game = $_GET['game'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT f.*, 
        (SELECT COUNT(*) FROM replies WHERE feedback_id = f.id) as reply_count
        FROM feedback f 
        WHERE 1=1";
$params = [];

if ($filter_type !== 'all') {
    $sql .= " AND f.type = :type";
    $params['type'] = $filter_type;
}

if ($filter_status !== 'all') {
    $sql .= " AND f.status = :status";
    $params['status'] = $filter_status;
}

if (!empty($filter_game)) {
    $sql .= " AND f.game LIKE :game";
    $params['game'] = '%' . $filter_game . '%';
}

if (!empty($search)) {
    $sql .= " AND (f.game LIKE :search OR f.message LIKE :search2 OR f.author LIKE :search3)";
    $params['search'] = '%' . $search . '%';
    $params['search2'] = '%' . $search . '%';
    $params['search3'] = '%' . $search . '%';
}

$sql .= " ORDER BY f.date DESC";

try {
    $query = $db->prepare($sql);
    $query->execute($params);
    $feedback_data = $query->fetchAll();
} catch (Exception $e) {
    error_log("Error fetching feedback: " . $e->getMessage());
    $feedback_data = [];
}

foreach ($feedback_data as &$feedback) {
    $sql_replies = "SELECT * FROM replies WHERE feedback_id = ? ORDER BY date ASC";
    $query_replies = $db->prepare($sql_replies);
    $query_replies->execute([$feedback['id']]);
    $feedback['replies'] = $query_replies->fetchAll();
}

$sql_games = "SELECT DISTINCT game FROM feedback ORDER BY game ASC";
$query_games = $db->prepare($sql_games);
$query_games->execute();
$games_list = $query_games->fetchAll();

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
  <title>Manage Feedback | GameBridge Admin</title>
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
      <a href="manage-feedback.php" class="nav-item active">
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
        <h1>Manage All Feedback</h1>
        <p class="subtitle">View, filter, and manage all feedback and reports</p>
      </div>
      <div class="header-actions">
        <a href="../feedback/feedback.php" class="btn btn-primary">➕ Add New</a>
      </div>
    </header>

    <div class="dashboard-card">
      <div class="card-header">
        <h2>🔍 Filters & Search</h2>
      </div>
      <div class="card-body">
        <form method="GET" action="" class="filter-form">
          <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" placeholder="Search game, message, or author..." 
                   value="<?php echo htmlspecialchars($search); ?>">
          </div>
          
          <div class="filter-group">
            <label>Type</label>
            <select name="type">
              <option value="all" <?php echo $filter_type === 'all' ? 'selected' : ''; ?>>All Types</option>
              <option value="feedback" <?php echo $filter_type === 'feedback' ? 'selected' : ''; ?>>Feedback Only</option>
              <option value="report" <?php echo $filter_type === 'report' ? 'selected' : ''; ?>>Reports Only</option>
            </select>
          </div>
          
          <div class="filter-group">
            <label>Status</label>
            <select name="status">
              <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Status</option>
              <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
              <option value="reviewed" <?php echo $filter_status === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
              <option value="fixed" <?php echo $filter_status === 'fixed' ? 'selected' : ''; ?>>Fixed</option>
            </select>
          </div>
          
          <div class="filter-group">
            <label>Game</label>
            <select name="game">
              <option value="">All Games</option>
              <?php foreach ($games_list as $game): ?>
              <option value="<?php echo htmlspecialchars($game['game']); ?>" 
                      <?php echo $filter_game === $game['game'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($game['game']); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="filter-actions">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="manage-feedback.php" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>
    </div>

    <!-- RESULTS -->
    <div class="dashboard-card">
      <div class="card-header">
        <h2>📝 Feedback Items (<?php echo count($feedback_data); ?>)</h2>
      </div>
      <div class="card-body">
        <?php if (empty($feedback_data)): ?>
        <div class="empty-state">
          <p>📭 No feedback found matching your filters</p>
        </div>
        <?php else: ?>
        <div class="feedback-table-container">
          <?php foreach ($feedback_data as $feedback): ?>
          <div class="feedback-row">
            <div class="feedback-row-header">
              <div class="feedback-row-title">
                <h3><?php echo htmlspecialchars($feedback['game']); ?></h3>
                <span class="feedback-type type-<?php echo $feedback['type']; ?>">
                  <?php echo $feedback['type'] === 'report' ? '🐛 Report' : '💭 Feedback'; ?>
                </span>
                <?php if ($feedback['type'] === 'report'): ?>
                <span class="status-badge status-<?php echo $feedback['status']; ?>">
                  <?php echo ucfirst($feedback['status']); ?>
                </span>
                <?php endif; ?>
              </div>
              <div class="feedback-row-meta">
                <span>@<?php echo htmlspecialchars($feedback['author']); ?></span>
                <span>•</span>
                <span><?php echo time_ago($feedback['date']); ?></span>
                <span>•</span>
                <span>💬 <?php echo $feedback['reply_count']; ?> replies</span>
              </div>
            </div>
            
            <div class="feedback-row-content">
              <p><?php echo htmlspecialchars($feedback['message']); ?></p>
            </div>
            
            <?php if (!empty($feedback['replies'])): ?>
            <div class="feedback-row-replies">
              <strong>Replies:</strong>
              <?php foreach ($feedback['replies'] as $reply): ?>
              <div class="reply-mini">
                <span class="reply-author">@<?php echo htmlspecialchars($reply['author']); ?>:</span>
                <span class="reply-text"><?php echo htmlspecialchars($reply['message']); ?></span>
                <span class="reply-time">(<?php echo time_ago($reply['date']); ?>)</span>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="feedback-row-actions">
              <?php if ($feedback['type'] === 'report'): ?>
              <select class="status-select-mini status-<?php echo $feedback['status']; ?>" 
                      onchange="updateStatusAdmin(<?php echo $feedback['id']; ?>, this.value)">
                <option value="pending" <?php echo $feedback['status'] === 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                <option value="reviewed" <?php echo $feedback['status'] === 'reviewed' ? 'selected' : ''; ?>>👁️ Reviewed</option>
                <option value="fixed" <?php echo $feedback['status'] === 'fixed' ? 'selected' : ''; ?>>✅ Fixed</option>
              </select>
              <?php endif; ?>
              
              <button class="btn-action btn-reply" onclick="showReplyFormAdmin(<?php echo $feedback['id']; ?>)">
                💬 Reply
              </button>
              
              <button class="btn-action btn-delete" onclick="deleteFeedbackAdmin(<?php echo $feedback['id']; ?>)">
                🗑️ Delete
              </button>
            </div>
          </div>
          <?php endforeach; ?>
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
