<?php

session_start();
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'TestUser';
    $_SESSION['role'] = 'player';
}

if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'player';
}

$user_role = $_SESSION['role'];

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
  <link rel="stylesheet" href="../../models/css/stylefeedback.css" />
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
      <?php if ($user_role === 'admin'): ?>
      <a href="../admin/dashboard.php" style="background: #00ff88; color: #1a1a2e; padding: 8px 15px; border-radius: 5px; font-weight: 600;">📊 Dashboard</a>
      <?php endif; ?>
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
      
      <form id="feedbackForm" onsubmit="submitFeedback(event)">
        <div class="form-group" id="gameNameGroup">
          <label for="gameName">Game Name</label>
          <input type="text" id="gameName" placeholder="e.g., Neon Runner">
          <span id="gameNameError" class="error-message" style="display: none; color: #ff3333; font-size: 0.85rem; margin-top: 5px;"></span>
        </div>

        <div class="form-group" id="gameSelectGroup" style="display: none;">
          <label for="gameSelect">Select Game</label>
          <select id="gameSelect">
            <option value="">-- Select a game --</option>
            <option value="Neon Runner">Neon Runner</option>
            <option value="Space Quest">Space Quest</option>
            <option value="Battle Arena">Battle Arena</option>
            <option value="Mystery Island">Mystery Island</option>
          </select>
          <span id="gameSelectError" class="error-message" style="display: none; color: #ff3333; font-size: 0.85rem; margin-top: 5px;"></span>
        </div>

        <div class="form-group">
          <label for="feedbackType">Type</label>
          <select id="feedbackType" onchange="toggleStatusField()">
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
          <textarea id="message" placeholder="Describe your feedback or report the issue..."></textarea>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 5px;">
            <span id="messageError" class="error-message" style="display: none; color: #ff3333; font-size: 0.85rem;"></span>
            <span id="charCounter" style="color: #aaa; font-size: 0.85rem;">0 / 1000</span>
          </div>
        </div>

        <div class="form-group">
          <label for="fileUpload">📎 Attach File (Optional)</label>
          <div style="display: flex; align-items: center; gap: 10px;">
            <input type="file" id="fileUpload" accept="image/*,.pdf,.doc,.docx" style="display: none;" onchange="handleFileSelect(event)">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('fileUpload').click()" style="padding: 8px 15px; font-size: 0.9rem;">
              📁 Choose File
            </button>
            <span id="fileName" style="color: #aaa; font-size: 0.9rem;">No file chosen</span>
          </div>
          <small style="color: #888; font-size: 0.8rem; display: block; margin-top: 5px;">
            Supported: Images (JPG, PNG, GIF), Documents (PDF, DOC, DOCX) - Max 5MB
          </small>
          <span id="fileError" class="error-message" style="display: none; color: #ff3333; font-size: 0.85rem; margin-top: 5px;"></span>
        </div>

        <div class="form-actions">
          <button type="button" class="btn btn-secondary" onclick="toggleNewFeedbackForm()">Cancel</button>
          <button type="submit" class="btn btn-primary">✅ Submit</button>
        </div>
      </form>
    </div>
  </section>
  <script src="../../models/js/feedback.js"></script>
  <script>
    // File upload handling
    let selectedFile = null;

    function handleFileSelect(event) {
      const file = event.target.files[0];
      const fileNameSpan = document.getElementById('fileName');
      const fileError = document.getElementById('fileError');
      
      // Clear previous errors
      fileError.textContent = '';
      fileError.style.display = 'none';
      
      if (!file) {
        fileNameSpan.textContent = 'No file chosen';
        selectedFile = null;
        return;
      }
      
      // Validate file size (5MB max)
      const maxSize = 5 * 1024 * 1024; // 5MB in bytes
      if (file.size > maxSize) {
        fileError.textContent = '⚠ File size must not exceed 5MB';
        fileError.style.display = 'block';
        event.target.value = '';
        fileNameSpan.textContent = 'No file chosen';
        selectedFile = null;
        return;
      }
      
      // Validate file type
      const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf', 
                            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
      if (!allowedTypes.includes(file.type)) {
        fileError.textContent = '⚠ Invalid file type. Please upload an image or document';
        fileError.style.display = 'block';
        event.target.value = '';
        fileNameSpan.textContent = 'No file chosen';
        selectedFile = null;
        return;
      }
      
      // File is valid
      selectedFile = file;
      fileNameSpan.textContent = file.name;
      fileNameSpan.style.color = '#00ff88';
    }

    // Additional validation functions
    function validateGameName() {
      const gameInput = document.getElementById('gameName');
      const errorMsg = document.getElementById('gameNameError');
      if (!gameInput) return true;
      
      const value = gameInput.value.trim();
      
      if (errorMsg) {
        errorMsg.textContent = '';
        errorMsg.style.display = 'none';
      }
      gameInput.classList.remove('error');
      
      if (value === '') {
        showError(gameInput, errorMsg, 'Game name is required');
        return false;
      }
      
      if (value.length < 2) {
        showError(gameInput, errorMsg, 'Game name must be at least 2 characters');
        return false;
      }
      
      if (value.length > 100) {
        showError(gameInput, errorMsg, 'Game name must not exceed 100 characters');
        return false;
      }
      
      gameInput.classList.add('success');
      return true;
    }

    function validateMessage() {
      const messageInput = document.getElementById('message');
      const errorMsg = document.getElementById('messageError');
      if (!messageInput) return true;
      
      const value = messageInput.value.trim();
      
      if (errorMsg) {
        errorMsg.textContent = '';
        errorMsg.style.display = 'none';
      }
      messageInput.classList.remove('error');
      
      if (value === '') {
        showError(messageInput, errorMsg, 'Message is required');
        return false;
      }
      
      if (value.length < 10) {
        showError(messageInput, errorMsg, 'Message must be at least 10 characters');
        return false;
      }
      
      if (value.length > 1000) {
        showError(messageInput, errorMsg, 'Message must not exceed 1000 characters');
        return false;
      }
      
      messageInput.classList.add('success');
      return true;
    }

    function validateGameSelect() {
      const gameSelect = document.getElementById('gameSelect');
      const errorMsg = document.getElementById('gameSelectError');
      if (!gameSelect) return true;
      
      if (errorMsg) {
        errorMsg.textContent = '';
        errorMsg.style.display = 'none';
      }
      gameSelect.classList.remove('error');
      
      if (gameSelect.value === '') {
        showError(gameSelect, errorMsg, 'Please select a game to report');
        return false;
      }
      
      gameSelect.classList.add('success');
      return true;
    }

    function showError(input, errorElement, message) {
      input.classList.add('error');
      if (errorElement) {
        errorElement.textContent = '⚠ ' + message;
        errorElement.style.display = 'block';
      }
    }

    function updateCharacterCount() {
      const message = document.getElementById('message');
      const counter = document.getElementById('charCounter');
      
      if (message && counter) {
        const current = message.value.length;
        const max = 1000;
        counter.textContent = current + ' / ' + max;
        
        if (current > max) {
          counter.style.color = '#ff3333';
        } else if (current > max * 0.9) {
          counter.style.color = '#ffcc00';
        } else {
          counter.style.color = '#aaa';
        }
      }
    }

    // Override submitFeedback to include validation
    function submitFeedback(event) {
      event.preventDefault();
      
      const type = document.getElementById('feedbackType').value;
      let isValid = true;
      
      // Validate based on type
      if (type === 'report') {
        if (!validateGameSelect()) isValid = false;
      } else {
        if (!validateGameName()) isValid = false;
      }
      
      // Always validate message
      if (!validateMessage()) isValid = false;
      
      // Stop if validation fails
      if (!isValid) {
        showNotification('Please fix the errors before submitting', 'error');
        return;
      }
      
      // Prepare form data
      const formData = new FormData();
      
      if (type === 'report') {
        formData.append('game', document.getElementById('gameSelect').value);
        formData.append('status', document.getElementById('status').value);
      } else {
        formData.append('game', document.getElementById('gameName').value);
      }
      
      formData.append('type', type);
      formData.append('message', document.getElementById('message').value);
      
      // Add file if selected
      if (selectedFile) {
        formData.append('file', selectedFile);
      }
      
      // Show loading state
      const submitBtn = event.target.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '⏳ Submitting...';
      submitBtn.disabled = true;
      
      fetch('../../controllers/submit.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(result => {
        showNotification('Feedback submitted successfully!', 'success');
        setTimeout(() => {
          location.reload();
        }, 1500);
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to submit feedback. Please try again.', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      });
    }

    // Event listeners for validation
    document.addEventListener('DOMContentLoaded', function() {
      const gameName = document.getElementById('gameName');
      const message = document.getElementById('message');
      const gameSelect = document.getElementById('gameSelect');
      const feedbackType = document.getElementById('feedbackType');
      
      if (gameName) {
        gameName.addEventListener('blur', validateGameName);
        gameName.addEventListener('input', function() {
          if (this.classList.contains('error')) {
            validateGameName();
          }
        });
      }
      
      if (message) {
        message.addEventListener('blur', validateMessage);
        message.addEventListener('input', function() {
          if (this.classList.contains('error')) {
            validateMessage();
          }
          updateCharacterCount();
        });
      }
      
      if (gameSelect) {
        gameSelect.addEventListener('change', function() {
          if (this.classList.contains('error')) {
            validateGameSelect();
          }
        });
      }
      
      if (feedbackType) {
        feedbackType.addEventListener('change', toggleStatusField);
      }
    });

    // Notification system
    function showNotification(message, type = 'info') {
      const existing = document.querySelector('.notification');
      if (existing) {
        existing.remove();
      }
      
      const notification = document.createElement('div');
      notification.className = `notification notification-${type}`;
      
      const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';
      notification.innerHTML = `
        <span class="notification-icon">${icon}</span>
        <span class="notification-message">${message}</span>
      `;
      
      // Add styles
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#00ff88' : type === 'error' ? '#ff3333' : '#4a9eff'};
        color: #1a1a2e;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        opacity: 0;
        transform: translateX(400px);
        transition: all 0.3s ease;
      `;
      
      document.body.appendChild(notification);
      
      setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
      }, 10);
      
      setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => notification.remove(), 300);
      }, 5000);
    }

    // Filter and search functions
    function filterFeedback(filterType, filterValue) {
      const cards = document.querySelectorAll('.feedback-card');
      
      cards.forEach(card => {
        let show = true;
        
        if (filterType === 'type' && filterValue !== 'all') {
          const type = card.querySelector('.feedback-type').textContent.toLowerCase();
          show = type.includes(filterValue);
        } else if (filterType === 'status' && filterValue !== 'all') {
          const status = card.querySelector('.status-select')?.value || 'none';
          show = status === filterValue;
        } else if (filterType === 'game') {
          const game = card.querySelector('h3').textContent.toLowerCase();
          show = game.includes(filterValue.toLowerCase());
        }
        
        card.style.display = show ? 'block' : 'none';
      });
    }

    function searchFeedback(searchTerm) {
      const cards = document.querySelectorAll('.feedback-card');
      const term = searchTerm.toLowerCase();
      
      cards.forEach(card => {
        const game = card.querySelector('h3').textContent.toLowerCase();
        const message = card.querySelector('p').textContent.toLowerCase();
        const author = card.querySelector('small').textContent.toLowerCase();
        
        const matches = game.includes(term) || message.includes(term) || author.includes(term);
        card.style.display = matches ? 'block' : 'none';
      });
    }
  </script>
</body>
</html>