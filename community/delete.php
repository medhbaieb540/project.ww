<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header("Location: community.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] === 'admin');

/* =========================
   DELETE POST
========================= */
if (isset($_POST['delete_post_id'])) {
    $post_id = (int) $_POST['delete_post_id'];

    // Récupérer l'auteur du post
    $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $owner_id = $stmt->fetchColumn();

    // Autorisé si admin ou propriétaire
    if ($owner_id && ($is_admin || $owner_id == $user_id)) {

        // Récupérer les IDs des commentaires liés à ce post
        $stmt = $pdo->prepare("SELECT id FROM comments WHERE post_id = ?");
        $stmt->execute([$post_id]);
        $comment_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Supprimer les replies liés à ces commentaires
        if (!empty($comment_ids)) {
            $in = implode(',', array_fill(0, count($comment_ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM replies WHERE comment_id IN ($in)");
            $stmt->execute($comment_ids);
        }

        // Supprimer les commentaires du post
        $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
        $stmt->execute([$post_id]);

        // Supprimer le post lui-même
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
    }

    header("Location: community.php");
    exit();
}

/* =========================
   DELETE COMMENT
========================= */
if (isset($_POST['delete_comment_id'])) {
    $comment_id = (int) $_POST['delete_comment_id'];

    // Récupérer l'auteur du commentaire
    $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    $owner_id = $stmt->fetchColumn();

    // Autorisé si admin ou propriétaire
    if ($owner_id && ($is_admin || $owner_id == $user_id)) {

        // Supprimer les replies de ce commentaire
        $stmt = $pdo->prepare("DELETE FROM replies WHERE comment_id = ?");
        $stmt->execute([$comment_id]);

        // Supprimer le commentaire
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);
    }

    header("Location: community.php");
    exit();
}

/* =========================
   DELETE REPLY
========================= */
if (isset($_POST['delete_reply_id'])) {
    $reply_id = (int) $_POST['delete_reply_id'];

    // Récupérer l'auteur de la reply
    $stmt = $pdo->prepare("SELECT user_id FROM replies WHERE id = ?");
    $stmt->execute([$reply_id]);
    $owner_id = $stmt->fetchColumn();

    // Autorisé si admin ou propriétaire
    if ($owner_id && ($is_admin || $owner_id == $user_id)) {

        // Supprimer la reply
        $stmt = $pdo->prepare("DELETE FROM replies WHERE id = ?");
        $stmt->execute([$reply_id]);
    }

    header("Location: community.php");
    exit();
}

/* =========================
   SÉCURITÉ
========================= */
header("Location: community.php");
exit();
