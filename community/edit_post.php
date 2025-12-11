<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header("Location: switchuser.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] === 'admin');

// Récupérer l'id du post
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: community.php");
    exit();
}

$post_id = (int) $_GET['id'];

// Charger le post
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header("Location: community.php");
    exit();
}

// Vérifier le droit (admin OU propriétaire)
if (!$is_admin && $post['user_id'] != $user_id) {
    header("Location: community.php");
    exit();
}

// Quand on soumet le formulaire
if (isset($_POST['content'])) {
    $content = trim($_POST['content']);

    if ($content !== '') {
        $stmt = $pdo->prepare("
            UPDATE posts 
            SET content = ?, is_edited = 1, edited_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$content, $post_id]);
    }

    header("Location: community.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Post</title>
<style>
body{background:#0c0c0c;color:#e6fff5;font-family:Arial;padding:40px}
.card{max-width:700px;margin:auto;background:#1a1a1a;border:1px solid #1aff8720;border-radius:16px;padding:24px}
h1{color:#1aff87;margin-bottom:18px}
textarea{width:100%;min-height:180px;background:#111;color:#fff;border-radius:12px;border:1px solid #1aff8720;padding:14px}
.btn{margin-top:14px;padding:9px 20px;border-radius:10px;border:none;cursor:pointer;background:#1aff87;color:#000;font-weight:600}
a.back{display:inline-block;margin-top:10px;color:#1aff87;text-decoration:none}
</style>
</head>
<body>

<div class="card">
  <h1>Edit Post</h1>

  <form method="POST">
    <textarea name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
    <br>
    <button class="btn" type="submit">Save changes</button>
  </form>

  <a class="back" href="community.php">⬅ Back</a>
</div>

</body>
</html>
