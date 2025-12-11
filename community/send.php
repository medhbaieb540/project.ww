<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: switchuser.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver = intval($_POST['receiver_id']);
    $msg = trim($_POST['message']);

    if ($receiver > 0 && $msg !== '') {
        $pdo->prepare("
            INSERT INTO messages(sender_id,receiver_id,message,is_read,created_at)
            VALUES (?,?,?,0,NOW())
        ")->execute([$user_id,$receiver,$msg]);

        header("Location: chat.php?user=".$receiver);
        exit();
    }
}

$users = $pdo->query("SELECT id, username FROM users WHERE id != $user_id")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Send Message</title>
<style>
body{background:#0c0c0c;color:white;font-family:Arial}
.container{max-width:600px;margin:40px auto;background:#1a1a1a;padding:22px;border-radius:18px}
textarea,select{width:100%;padding:12px;background:#111;color:white;border-radius:12px}
.btn{background:#1aff87;color:black;padding:8px 16px;border-radius:10px;margin-top:10px}
</style>
</head>
<body>

<div class="container">

<form method="POST">
<select name="receiver_id" required>
<option value="">-- Select user --</option>
<?php foreach($users as $u): ?>
<option value="<?= $u['id'] ?>">@<?= $u['username'] ?></option>
<?php endforeach; ?>
</select>

<textarea name="message" required></textarea>
<button class="btn">Send</button>
</form>

</div>

</body>
</html>
