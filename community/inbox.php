<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: switchuser.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role     = $_SESSION['role'];

/* ✅ GET ONLY ONE CONVERSATION PER USER */
$stmt = $pdo->prepare("
    SELECT 
        CASE 
            WHEN sender_id = :me THEN receiver_id 
            ELSE sender_id 
        END AS other_id,
        MAX(created_at) AS last_time
    FROM messages
    WHERE sender_id = :me OR receiver_id = :me
    GROUP BY other_id
    ORDER BY last_time DESC
");
$stmt->execute(['me' => $user_id]);
$conversations_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ✅ FETCH USER NAMES + LAST MESSAGE */
$conversations = [];
foreach ($conversations_raw as $row) {
    $other_id = $row['other_id'];

    $userStmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $userStmt->execute([$other_id]);
    $other_name = $userStmt->fetchColumn();

    $msgStmt = $pdo->prepare("
        SELECT message, created_at 
        FROM messages
        WHERE (sender_id=? AND receiver_id=?) 
           OR (sender_id=? AND receiver_id=?)
        ORDER BY created_at DESC LIMIT 1
    ");
    $msgStmt->execute([$user_id, $other_id, $other_id, $user_id]);
    $lastMsg = $msgStmt->fetch(PDO::FETCH_ASSOC);

    $conversations[] = [
        'other_id' => $other_id,
        'other_name' => $other_name,
        'last_msg' => $lastMsg['message'],
        'last_time' => $lastMsg['created_at']
    ];
}

/* ✅ UNREAD COUNTS */
$stmtUnread = $pdo->prepare("
    SELECT sender_id, COUNT(*) AS unread_count
    FROM messages
    WHERE receiver_id = ? AND is_read = 0
    GROUP BY sender_id
");
$stmtUnread->execute([$user_id]);

$unread = [];
while ($u = $stmtUnread->fetch()) {
    $unread[$u['sender_id']] = $u['unread_count'];
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Inbox | GameBridge</title>
<style>
body{background:#0c0c0c;color:#fff;font-family:Arial}
header{background:#111;padding:15px 6%;border-bottom:2px solid #1aff87;display:flex;justify-content:space-between}
nav a{color:white;margin-left:20px;text-decoration:none}
nav a:hover{color:#1aff87}
.user-badge{border:1px solid #1aff87;padding:6px 14px;border-radius:20px;color:#1aff87}
.container{max-width:900px;margin:40px auto;background:#1a1a1a;border-radius:18px;padding:22px}
.conv{border-bottom:1px solid #1aff8720;padding:14px 0}
.badge{background:red;color:white;padding:2px 8px;border-radius:10px;font-size:12px;margin-left:8px}
a{color:#1aff87;text-decoration:none}
.btn{background:#1aff87;color:black;padding:8px 16px;border-radius:10px;font-weight:bold;display:inline-block;margin-bottom:15px}
.small{font-size:12px;color:#aaa}
</style>
</head>
<body>

<header>
<h2>🎮 GameBridge</h2>
<div style="display:flex;gap:15px;align-items:center;">
<span class="user-badge">@<?= $username ?> (<?= ucfirst($role) ?>)</span>
<nav>
<a href="community.php">Community</a>
<a href="inbox.php">Messages</a>
<a href="switchuser.php">Switch User</a>
</nav>
</div>
</header>

<div class="container">
<a class="btn" href="send.php">➕ New Message</a>

<?php foreach ($conversations as $conv): ?>
<div class="conv">
<a href="chat.php?user=<?= $conv['other_id'] ?>">
@<?= htmlspecialchars($conv['other_name']) ?>
</a>

<?php if (!empty($unread[$conv['other_id']])): ?>
<span class="badge"><?= $unread[$conv['other_id']] ?></span>
<?php endif; ?>

<div class="small"><?= htmlspecialchars($conv['last_msg']) ?></div>
<div class="small"><?= $conv['last_time'] ?></div>
</div>
<?php endforeach; ?>

</div>
</body>
</html>
