<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: community.php");
    exit();
}

if (!isset($_POST['post_content']) || empty(trim($_POST['post_content']))) {
    header("Location: community.php");
    exit();
}

$content = trim($_POST['post_content']);
$user_id = $_SESSION['user_id'];


$imageName = null;

if (!empty($_FILES['post_image']['name'])) {
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imageName = time() . "_" . basename($_FILES["post_image"]["name"]);
    move_uploaded_file($_FILES["post_image"]["tmp_name"], $uploadDir . $imageName);
}

$stmt = $pdo->prepare("
    INSERT INTO posts (user_id, content, image) 
    VALUES (?, ?, ?)
");
$stmt->execute([$user_id, $content, $imageName]);

header("Location: community.php");
exit();
