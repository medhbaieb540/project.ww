<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$game_id = $_POST['game_id'] ?? $_GET['game_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$game_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Game ID required.']);
    exit;
}

try {
    // Check if user has already played this game
    $stmt = $pdo->prepare("SELECT * FROM user_games WHERE user_id = ? AND game_id = ?");
    $stmt->execute([$user_id, $game_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update play count and last played date
        $stmt = $pdo->prepare("UPDATE user_games SET play_count = play_count + 1, last_played = NOW() WHERE user_id = ? AND game_id = ?");
        $stmt->execute([$user_id, $game_id]);
    } else {
        // Create new user_games entry
        $stmt = $pdo->prepare("INSERT INTO user_games (user_id, game_id, play_count, last_played) VALUES (?, ?, 1, NOW())");
        $stmt->execute([$user_id, $game_id]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Game added to your profile.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
