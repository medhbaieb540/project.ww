<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: community.php");
    exit();
}

$username  = $_SESSION['username'];
$role      = $_SESSION['role'];
$user_id   = $_SESSION['user_id'];
$is_admin  = ($role === 'admin');

$posts = $pdo->query("
SELECT posts.*, users.username
FROM posts
JOIN users ON posts.user_id = users.id
WHERE posts.is_archived = 1
ORDER BY posts.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<title>Archived Posts | GameBridge</title>
<style>
body{background:#0c0c0c;color:white;font-family:Arial}
header{background:#111;padding:15px 6%;border-bottom:2px solid #1aff87;display:flex;justify-content:space-between}
nav a{color:white;margin-left:16px;text-decoration:none}
nav a:hover{color:#1aff87}
.card{background:#1a1a1a;padding:20px;margin:20px 6%;border-radius:16px}
.btn{background:#1aff87;color:black;padding:7px 16px;border-radius:10px;border:none;cursor:pointer}
.user-badge{border:1px solid #1aff87;padding:6px 14px;border-radius:20px;color:#1aff87}
</style>
</head>

<body>

<header>
<h2>📦 Archived Posts</h2>
<div>
<span class="user-badge">@<?= $username ?> (<?= ucfirst($role) ?>)</span>
<nav>
<a href="community.php">Community</a>
<a href="archive.php">Archive</a>
</nav>
</div>
</header>

<?php foreach ($posts as $post): ?>
<div class="card">
<b>@<?= htmlspecialchars($post['username']) ?></b><br>
<p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

<form method="POST" action="unarchive_post.php">
  <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
  <button class="btn">Restore</button>
</form>
</div>
<?php endforeach; ?>

</body>
</html>
