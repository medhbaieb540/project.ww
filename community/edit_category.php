<?php
session_start();
require 'config.php';

/* =========================
   SECURITY: ADMIN ONLY
========================= */
if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header("Location: switchuser.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: community.php");
    exit();
}

/* =========================
   CHECK POST DATA
========================= */
if (!isset($_POST['post_id']) || !isset($_POST['category'])) {
    header("Location: community.php");
    exit();
}

$post_id  = (int) $_POST['post_id'];
$category = trim($_POST['category']);

$allowed = ['Action', 'Adventure', 'Racing', 'Strategy'];

if (!in_array($category, $allowed)) {
    header("Location: community.php");
    exit();
}

/* =========================
   UPDATE CATEGORY
========================= */
$stmt = $pdo->prepare("
    UPDATE posts 
    SET category = ?
    WHERE id = ?
");
$stmt->execute([$category, $post_id]);

header("Location: community.php");
exit();
