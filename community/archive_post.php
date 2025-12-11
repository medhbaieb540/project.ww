<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header("Location: community.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] === 'admin');

if (!isset($_POST['post_id'])) {
    header("Location: community.php");
    exit();
}

$post_id = (int) $_POST['post_id'];

$stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id=?");
$stmt->execute([$post_id]);
$owner = $stmt->fetchColumn();

if ($is_admin || $owner == $user_id) {
    $pdo->prepare("UPDATE posts SET is_archived = 1 WHERE id=?")->execute([$post_id]);
}

header("Location: community.php");
exit();
