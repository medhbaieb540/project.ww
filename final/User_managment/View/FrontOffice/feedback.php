<?php
session_set_cookie_params(['path' => '/FINAL/', 'httponly' => true]);
session_start();

$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
if ($userId === null) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../Controller/FeedbackController.php';

$controller = new FeedbackController($pdo);
$feedbackList = $controller->getFeedbackWithReplies();
$stats = $controller->getStats();

$username = $_SESSION['username'] ?? 'Player';
$userRole = $_SESSION['user_role'] ?? ($_SESSION['role'] ?? 'player');
$returnToAdmin = isset($_GET['from']) && strpos($_GET['from'], 'admin_feedback') === 0;
$backofficeUrl = '../BackOffice/admin_feedback.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GameBridge | Feedback</title>
  <link rel="stylesheet" href="../../public/css/frontoffice-header.css">
  <link rel="stylesheet" href="../../public/css/stylefeedback.css">
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
    <a href="feedback.php" class="active">Feedback</a>
    <a href="profile.php">My Profile</a>
     <a href="logout.php"
         onclick="return confirm('Are you sure you want to logout?');"
         class="logout-btn">Logout</a>
    
  </nav>
</header>

<section>
  <h2>Game Feedback & Reports</h2>
  <p class="description">Share your feedback or report issues. Help developers improve your gaming experience.</p>

  <?php if ($returnToAdmin): ?>
  <div style="text-align:center; margin: 12px 0 6px;">
    <a href="<?php echo $backofficeUrl; ?>" class="btn btn-secondary" style="padding:10px 16px; border-radius:8px; text-decoration:none; background:#222; color:#fff; border:1px solid #1aff87;">← Return to Backoffice</a>
  </div>
  <?php endif; ?>

  <div style="text-align:center; margin-bottom: 20px;">
    <span class="role-badge role-<?php echo htmlspecialchars($userRole); ?>">Logged in as <?php echo htmlspecialchars(ucfirst($userRole)); ?></span>
    <span class="pill">Total: <?php echo (int) $stats['total']; ?></span>
    <span class="pill">Reports: <?php echo (int) $stats['reports']; ?></span>
    <span class="pill">Feedback: <?php echo (int) $stats['feedback']; ?></span>
  </div>

  <div class="actions-container">
    <button class="btn btn-primary" onclick="document.getElementById('newFeedbackForm').classList.toggle('active')">➕ Add Feedback</button>
  </div>

  <div class="new-feedback-container" id="newFeedbackForm">
    <h3 style="color: var(--accent); margin-bottom: 12px;">Submit Feedback or Bug Report</h3>
    <form id="feedbackForm">
      <div class="form-group">
        <label for="game">Game Name</label>
        <input type="text" id="game" name="game" maxlength="100" placeholder="e.g., Neon Runner" required>
      </div>

      <div class="form-group">
        <label for="type">Type</label>
        <select name="type" id="type">
          <option value="feedback">Feedback</option>
          <option value="report">Report</option>
        </select>
      </div>

      <?php if (in_array($userRole, ['developer', 'admin'], true)): ?>
      <div class="form-group">
        <label for="status">Initial Status</label>
        <select name="status" id="status">
          <option value="pending">Pending</option>
          <option value="reviewed">Reviewed</option>
          <option value="fixed">Fixed</option>
        </select>
      </div>
      <?php endif; ?>

      <div class="form-group">
        <label for="message">Message</label>
        <textarea id="message" name="message" maxlength="1000" placeholder="Describe your feedback or the issue..." required></textarea>
      </div>

      <div class="form-actions">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('newFeedbackForm').classList.remove('active')">Cancel</button>
        <button type="submit" class="btn btn-primary">Submit</button>
      </div>
    </form>
  </div>

  <div class="filters-bar">
    <input type="text" id="feedbackSearch" placeholder="Search feedback...">
  </div>

  <div class="feedback-grid">
    <?php foreach ($feedbackList as $item): ?>
      <div class="feedback-card">
        <div class="card-meta">
          <div class="left">
            <span class="badge badge-<?php echo htmlspecialchars($item['type']); ?>"><?php echo htmlspecialchars(ucfirst($item['type'])); ?></span>
            <span class="badge badge-<?php echo htmlspecialchars($item['status']); ?>"><?php echo htmlspecialchars(ucfirst($item['status'])); ?></span>
          </div>
          <div class="right">
            <span class="reply-count"><?php echo (int) ($item['reply_count'] ?? 0); ?> replies</span>
          </div>
        </div>

        <h3><?php echo htmlspecialchars($item['game']); ?></h3>
        <p><?php echo nl2br(htmlspecialchars($item['message'])); ?></p>
        <small>By @<?php echo htmlspecialchars($item['author']); ?> • <?php echo date('M j, Y', strtotime($item['date'])); ?></small>

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

        <form class="reply-form reply-form-inline" data-feedback-id="<?php echo (int) $item['id']; ?>">
          <input type="text" name="reply_message" placeholder="Add a reply..." maxlength="500">
          <button type="submit" class="btn-small btn-reply">Reply</button>
        </form>

        <?php if (in_array($userRole, ['developer', 'admin'], true)): ?>
          <select class="status-select status-select-inline" data-feedback-id="<?php echo (int) $item['id']; ?>" data-current="<?php echo htmlspecialchars($item['status']); ?>">
            <option value="pending" <?php echo $item['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="reviewed" <?php echo $item['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
            <option value="fixed" <?php echo $item['status'] === 'fixed' ? 'selected' : ''; ?>>Fixed</option>
          </select>
        <?php endif; ?>

        <?php if ($userRole === 'admin'): ?>
          <div class="card-actions">
            <button type="button" class="btn-small btn-delete delete-feedback" data-feedback-id="<?php echo (int) $item['id']; ?>">Delete</button>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<script src="../../public/js/feedback.js"></script>
</body>
</html>
