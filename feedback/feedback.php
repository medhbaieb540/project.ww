<?php

// Simple session management for testing
session_start();
require 'config.php';

// Mock user data for testing (no login required)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'TestUser';
    $_SESSION['role'] = 'player'; // Can be: player, developer, admin
}

$user_role = $_SESSION['role'];

// Fetch feedback from database instead of session
$db = Config::getConnexion();
$sql = "SELECT f.*, 
        (SELECT COUNT(*) FROM replies WHERE feedback_id = f.id) as reply_count
        FROM feedback f 
        ORDER BY f.date DESC";

try {
    $query = $db->prepare($sql);
    $query->execute();
    $feedback_data = $query->fetchAll();
} catch (Exception $e) {
    error_log("Error fetching feedback: " . $e->getMessage());
    $feedback_data = [];
}

// Fetch replies for each feedback
foreach ($feedback_data as &$feedback) {
    $sql_replies = "SELECT * FROM replies WHERE feedback_id = ? ORDER BY date DESC";
    $query_replies = $db->prepare($sql_replies);
    $query_replies->execute([$feedback['id']]);
    $feedback['replies'] = $query_replies->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GameBridge | Game Feedback</title>
  <link rel="stylesheet" href="stylefeedback.css" />
</head>
<body>
  <header>
    <div class="logo-container">
      <h1>GameBridge</h1>
    </div>
    <nav>
      <a href="../index.html">Home</a>
      <a href="games.html">Games</a>
      <a href="feedback.php" class="active">Feedback</a>
    </nav>
  </header>

  <section>
    <h2>💬 Game Feedback & Reports</h2>
    <p class="description">
      Share your feedback or report issues. Help developers improve your gaming experience!
    </p>

    <div style="text-align: center;">
      <span class="role-badge role-<?php echo $user_role; ?>">
         Current Role: <?php echo ucfirst($user_role); ?>
      </span>
    </div>

    <div class="feedback-grid">
      <?php foreach ($feedback_data as $feedback): ?>
      <div class="feedback-card">
        <h3><?php echo htmlspecialchars($feedback['game']); ?></h3>
        
        <span class="feedback-type type-<?php echo $feedback['type']; ?>">
          <?php echo $feedback['type'] === 'report' ? '🐛 Report' : '💭 Feedback'; ?>
        </span>
        
        <p><?php echo htmlspecialchars($feedback['message']); ?></p>
        
        <small>
          By @<?php echo htmlspecialchars($feedback['author']); ?> • 
          <?php 
          $time_ago = time() - strtotime($feedback['date']);
          if ($time_ago < 3600) echo floor($time_ago / 60) . ' min ago';
          elseif ($time_ago < 86400) echo floor($time_ago / 3600) . ' hr ago';
          elseif ($time_ago < 604800) echo floor($time_ago / 86400) . ' days ago';
          else echo floor($time_ago / 604800) . ' weeks ago';
          ?>
        </small>
        
        <?php if ($feedback['type'] === 'report' && ($user_role === 'developer' || $user_role === 'admin')): ?>
        <select class="status-select status-<?php echo $feedback['status']; ?>" 
                onchange="updateStatus(<?php echo $feedback['id']; ?>, this.value)">
          <option value="pending" <?php echo $feedback['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
          <option value="reviewed" <?php echo $feedback['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
          <option value="fixed" <?php echo $feedback['status'] === 'fixed' ? 'selected' : ''; ?>>Fixed</option>
        </select>
        <?php elseif ($feedback['type'] === 'report'): ?>
        <div style="margin-top: 10px;">
          <strong>Status:</strong> 
          <span class="status-<?php echo $feedback['status']; ?>" style="font-weight: 600;">
            <?php echo ucfirst($feedback['status']); ?>
          </span>
        </div>
        <?php endif; ?>

        <?php if (!empty($feedback['replies'])): ?>
        <div class="replies-section">
          <strong style="color: #aaa; font-size: 0.85rem;">💬 Replies:</strong>
          <?php foreach ($feedback['replies'] as $reply): ?>
          <div class="reply-item">
            <div class="reply-author">@<?php echo htmlspecialchars($reply['author']); ?></div>
            <div class="reply-message"><?php echo htmlspecialchars($reply['message']); ?></div>
            <div class="reply-time"><?php echo date('M j, Y', strtotime($reply['date'])); ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="card-actions">
          <button class="btn-small btn-reply" onclick="showReplyForm(<?php echo $feedback['id']; ?>)">
            💬 Reply
          </button>
          <?php if ($user_role === 'admin'): ?>
          <button class="btn-small btn-delete" onclick="deleteFeedback(<?php echo $feedback['id']; ?>)">
            🗑️ Delete
          </button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="actions-container">
      <button class="btn btn-primary" onclick="toggleNewFeedbackForm()">
        ➕ Add New Feedback / Report
      </button>
      <a href="games.html" class="btn btn-secondary">🎮 Back to Games</a>
    </div>

    <div class="new-feedback-container" id="newFeedbackForm">
      <h3 style="color: var(--accent); margin-bottom: 20px;">📝 Submit Feedback or Report</h3>
      
      <form onsubmit="submitFeedback(event)">
        <div class="form-group">
          <label for="gameName">Game Name</label>
          <input type="text" id="gameName" placeholder="e.g., Neon Runner" required>
        </div>

        <div class="form-group">
          <label for="feedbackType">Type</label>
          <select id="feedbackType" onchange="toggleStatusField()" required>
            <option value="feedback">💭 Feedback (General comment)</option>
            <option value="report">🐛 Report (Bug or issue)</option>
          </select>
        </div>

        <div class="form-group" id="statusGroup" style="display: none;">
          <label for="status">Initial Status (for reports)</label>
          <select id="status">
            <option value="pending">Pending</option>
            <option value="reviewed">Reviewed</option>
            <option value="fixed">Fixed</option>
          </select>
        </div>

        <div class="form-group">
          <label for="message">Your Message</label>
          <textarea id="message" placeholder="Describe your feedback or report the issue..." required></textarea>
        </div>

        <div class="form-actions">
          <button type="button" class="btn btn-secondary" onclick="toggleNewFeedbackForm()">Cancel</button>
          <button type="submit" class="btn btn-primary">✅ Submit</button>
        </div>
      </form>
    </div>
  </section>
  <script src="feedback.js"></script>
</body>
</html>