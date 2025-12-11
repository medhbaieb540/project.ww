<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: inbox.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$is_admin = ($role === 'admin');

/* ✅ DELETE MESSAGE */
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id    = (int) $_POST['id'];
    $other = (int) $_POST['user'];

    $stmt = $pdo->prepare("SELECT sender_id FROM messages WHERE id=?");
    $stmt->execute([$id]);
    $owner = $stmt->fetchColumn();

    if ($owner == $user_id || $is_admin) {
        $pdo->prepare("DELETE FROM messages WHERE id=?")->execute([$id]);
    }

    header("Location: chat.php?user=".$other);
    exit();
}

/* ✅ UPDATE MESSAGE (WITH FILTER) */
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id    = (int) $_POST['id'];
    $other = (int) $_POST['user'];
    $text  = filterBadWords(trim($_POST['message']));

    if ($text !== '') {
        $pdo->prepare("
            UPDATE messages SET message=?, edited_at=NOW() WHERE id=?
        ")->execute([$text, $id]);
    }

    header("Location: chat.php?user=".$other);
    exit();
}
