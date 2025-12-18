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
$inbox = $controller->getInbox((int) $userId);

$selectedUserId = isset($_GET['user']) ? (int) $_GET['user'] : null;
if ($selectedUserId === null && !empty($inbox)) {
    $first = $inbox[0];
    $selectedUserId = ($first['sender_id'] == $userId) ? (int) $first['receiver_id'] : (int) $first['sender_id'];
}

$conversation = $selectedUserId ? $controller->getConversation((int) $userId, $selectedUserId) : [];
$unreadCount = $controller->getUnreadCount((int) $userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GameBridge | Messages</title>
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
    <a class="active" href="chat.php">Messages<?php echo $unreadCount ? ' (' . (int) $unreadCount . ')' : ''; ?></a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<section>
  <h2>Messages</h2>
  <div style="display:grid; grid-template-columns: 280px 1fr; gap:20px; align-items:flex-start;">
    <div style="background:#0f0f0f; border:1px solid #1aff8715; border-radius:10px; padding:12px; max-height:70vh; overflow-y:auto;">
      <h3 style="color:var(--accent); margin-bottom:10px;">Inbox</h3>
      <?php if (empty($inbox)): ?>
        <p style="color:#aaa;">No conversations yet.</p>
      <?php else: ?>
        <?php foreach ($inbox as $row): ?>
          <?php
            $otherId = ($row['sender_id'] == $userId) ? (int)$row['receiver_id'] : (int)$row['sender_id'];
            $otherName = $row['other_username'] ?? 'User ' . $otherId;
          ?>
          <a href="chat.php?user=<?php echo $otherId; ?>" style="display:block; padding:10px 12px; margin-bottom:8px; background:<?php echo ($selectedUserId === $otherId) ? '#1a1a1a' : '#111'; ?>; border:1px solid #1aff8715; border-radius:8px; color:#fff; text-decoration:none;">
            <strong>@<?php echo htmlspecialchars($otherName); ?></strong><br>
            <span style="color:#aaa; font-size:0.9rem;">Tap to open</span>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div style="background:#0f0f0f; border:1px solid #1aff8715; border-radius:10px; padding:12px; min-height:60vh; display:flex; flex-direction:column;">
      <div class="messages-container" id="messagesBox" style="flex:1;">
        <?php if (empty($conversation)): ?>
          <p style="color:#aaa;">Select a conversation to start chatting.</p>
        <?php else: ?>
          <?php foreach ($conversation as $msg): ?>
            <div class="message <?php echo ((int)$msg['sender_id'] === (int)$userId) ? 'user' : 'other'; ?>" data-message-id="<?php echo (int) $msg['id']; ?>">
              <div class="message-author">@<?php echo htmlspecialchars(((int)$msg['sender_id'] === (int)$userId) ? 'You' : ($msg['sender_name'] ?? 'User')); ?></div>
              <div class="message-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
              <div class="message-time"><?php echo date('M j, Y H:i', strtotime($msg['created_at'])); ?></div>
              <?php if ((int)$msg['sender_id'] === (int)$userId): ?>
                <button class="message-delete-btn" data-action="delete-message" data-id="<?php echo (int) $msg['id']; ?>">Delete</button>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <?php if ($selectedUserId): ?>
        <form id="sendMessageForm" style="margin-top:10px; display:flex; gap:10px;">
          <input type="hidden" name="receiver_id" value="<?php echo (int) $selectedUserId; ?>">
          <input type="text" name="message" placeholder="Type a message..." maxlength="1000" style="flex:1; padding:12px 14px; border-radius:8px; border:1px solid #1aff8715; background:#111; color:#fff;">
          <button type="submit" class="btn" style="padding:12px 14px;">Send</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
  const box = document.getElementById('messagesBox');
  if (box) box.scrollTop = box.scrollHeight;

  document.querySelectorAll('[data-action="delete-message"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      if (!confirm('Delete this message?')) return;
      fetch('../../Controller/delete_message_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `message_id=${encodeURIComponent(id)}`
      }).then(r => r.json()).then(data => {
        if (data.success) window.location.reload();
      });
    });
  });

  const form = document.getElementById('sendMessageForm');
  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const receiver = form.querySelector('input[name="receiver_id"]').value;
      const message = form.querySelector('input[name="message"]').value.trim();
      if (!message) return;
      fetch('../../Controller/send_message_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `receiver_id=${encodeURIComponent(receiver)}&message=${encodeURIComponent(message)}`
      }).then(r => r.json()).then(data => {
        if (data.success) {
          window.location.reload();
        }
      });
    });
  }
</script>
</body>
</html>
