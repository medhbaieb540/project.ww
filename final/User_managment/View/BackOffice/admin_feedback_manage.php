<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../FrontOffice/feedback.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../Controller/FeedbackController.php';

$controller = new FeedbackController($pdo);

$filter_type = $_GET['type'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';
$filter_game = $_GET['game'] ?? '';
$search = $_GET['search'] ?? '';

$filters = [];
if ($filter_type !== 'all') $filters['type'] = $filter_type;
if ($filter_status !== 'all') $filters['status'] = $filter_status;
if (!empty($filter_game)) $filters['game'] = $filter_game;
if (!empty($search)) $filters['search'] = $search;

$feedback_data = $controller->getFeedbackWithReplies($filters);

// Build games list for filter
$gamesStmt = $pdo->query('SELECT DISTINCT game FROM feedback ORDER BY game ASC');
$games_list = $gamesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Feedback | GameBridge Admin</title>
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
      <a href="admin_feedback_manage.php" class="nav-link active" style="padding:8px 0;">Manage</a>
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
    <header class="content-header">
      <div>
        <h1>Manage All Feedback</h1>
        <p class="subtitle">View, filter, and manage all feedback and reports</p>
      </div>
      <div class="header-actions">
        <a href="../FrontOffice/feedback.php?from=admin_feedback_manage" class="btn btn-primary">Submit New</a>
      </div>
    </header>

    <div class="dashboard-card">
      <div class="card-header">
        <h2>Filters & Search</h2>
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
            <a href="admin_feedback_manage.php" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>
    </div>

    <div class="dashboard-card">
      <div class="card-header">
        <h2>Feedback Items (<?php echo count($feedback_data); ?>)</h2>
      </div>
      <div class="card-body">
        <?php if (empty($feedback_data)): ?>
        <div class="empty-state">
          <p>No feedback found matching your filters</p>
        </div>
        <?php else: ?>
          <div class="feedback-table">
            <?php foreach ($feedback_data as $item): ?>
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

                <?php if (!empty($item['replies'])): ?>
                  <div class="replies-section">
                    <strong style="color:#aaa; font-size:0.9rem;">Replies</strong>
                    <?php foreach ($item['replies'] as $reply): ?>
                      <div class="reply-item">
                        <div class="reply-author">@<?php echo htmlspecialchars($reply['author']); ?></div>
                        <div class="reply-message"><?php echo nl2br(htmlspecialchars($reply['message'])); ?></div>
                        <div class="reply-time"><?php echo date('M j, Y', strtotime($reply['date'])); ?></div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <div class="row-actions" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                  <button class="btn-action btn-reply" onclick="showReplyFormAdmin(<?php echo (int)$item['id']; ?>)">Reply</button>
                  <select class="status-select" onchange="updateStatusAdmin(<?php echo (int)$item['id']; ?>, this.value)">
                    <option value="pending" <?php echo $item['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="reviewed" <?php echo $item['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                    <option value="fixed" <?php echo $item['status'] === 'fixed' ? 'selected' : ''; ?>>Fixed</option>
                  </select>
                  <button class="btn-action btn-delete" onclick="deleteFeedbackAdmin(<?php echo (int)$item['id']; ?>)">Delete</button>
                </div>
              </div>
            <?php endforeach; ?>
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
