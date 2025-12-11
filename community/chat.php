<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: switchuser.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$is_admin = ($role === 'admin');

$other_id = intval($_GET['user'] ?? 0);
if ($other_id <= 0) {
    header("Location: inbox.php");
    exit();
}

/* ✅ MARK AS READ */
$pdo->prepare("
    UPDATE messages SET is_read=1 
    WHERE sender_id=? AND receiver_id=?
")->execute([$other_id, $user_id]);

/* ✅ SEND MESSAGE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $msg = trim($_POST['message']);
    if ($msg !== '') {
        $pdo->prepare("
            INSERT INTO messages(sender_id,receiver_id,message,is_read,created_at)
            VALUES (?,?,?,0,NOW())
        ")->execute([$user_id, $other_id, $msg]);

        header("Location: chat.php?user=".$other_id);
        exit();
    }
}

/* ✅ FETCH USER */
$stmt = $pdo->prepare("SELECT username FROM users WHERE id=?");
$stmt->execute([$other_id]);
$otherUser = $stmt->fetchColumn();

/* ✅ FETCH CONVERSATION */
$stmt = $pdo->prepare("
    SELECT * FROM messages
    WHERE (sender_id=? AND receiver_id=?) 
       OR (sender_id=? AND receiver_id=?)
    ORDER BY created_at ASC
");
$stmt->execute([$user_id,$other_id,$other_id,$user_id]);
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Chat | GameBridge</title>
<style>
body{background:#0c0c0c;color:white;font-family:Arial}
.chat{max-width:800px;margin:40px auto;background:#1a1a1a;border-radius:18px;padding:20px}
.msg-me{text-align:right;color:#1aff87;margin-bottom:10px}
.msg-them{text-align:left;margin-bottom:10px}
textarea{width:100%;padding:12px;background:#111;color:white;border-radius:12px}
.btn{background:#1aff87;color:black;padding:8px 16px;border-radius:10px;margin-top:10px;border:none;cursor:pointer}
.small{font-size:12px;color:#aaa}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
.user-badge{border:1px solid #1aff87;padding:6px 14px;border-radius:20px;color:#1aff87}
.actions a{margin-left:8px;color:#57a0ff;text-decoration:none}
.actions form{display:inline}
.actions button{background:none;border:none;color:#ff5b5b;cursor:pointer}
</style>
</head>

<body>

<div class="chat">

<div class="header">
<h3>Chat with @<?= htmlspecialchars($otherUser) ?></h3>
<span class="user-badge">@<?= $username ?> (<?= ucfirst($role) ?>)</span>
</div>

<?php foreach ($messages as $m): ?>
<div class="<?= ($m['sender_id']==$user_id)?'msg-me':'msg-them' ?>">
<?= nl2br(htmlspecialchars($m['message'])) ?>

<div class="small">
<?= $m['created_at'] ?> 
<?= ($m['is_read'] && $m['sender_id']==$user_id) ? '• Seen' : '' ?>
</div>

<?php if ($m['sender_id']==$user_id || $is_admin): ?>
<div class="actions">
  <a href="message_actions.php?action=edit&id=<?= $m['id'] ?>&user=<?= $other_id ?>">✏</a>

  <form method="POST" action="message_actions.php">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" value="<?= $m['id'] ?>">
    <input type="hidden" name="user" value="<?= $other_id ?>">
    <button type="submit">🗑</button>
  </form>
</div>
<?php endif; ?>
</div>
<?php endforeach; ?>

<form method="POST">
<textarea name="message" placeholder="Type your message..." required></textarea>
<button class="btn" name="send_message">Send</button>
</form>

<br>
<a href="inbox.php" style="color:#1aff87;">⬅ Back to Inbox</a>

</div>
</body>
</html>
 