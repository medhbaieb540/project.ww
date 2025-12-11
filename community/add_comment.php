<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: community.php");
    exit();
}

if (
    !isset($_POST['post_id']) || 
    !isset($_POST['comment_text']) || 
    empty(trim($_POST['comment_text']))
) {
    header("Location: community.php");
    exit();
}

$post_id = intval($_POST['post_id']);
$content = trim($_POST['comment_text']);
$user_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("
    INSERT INTO comments (post_id, user_id, content) 
    VALUES (?, ?, ?)
");
$stmt->execute([$post_id, $user_id, $content]);

header("Location: community.php");
exit();
