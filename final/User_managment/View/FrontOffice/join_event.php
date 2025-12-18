<?php
// join_event.php - Handle event participation (user joins event)
session_start();
header('Content-Type: application/json');
$pdo = require __DIR__ . '/../../config/db.php';

$response = ['success' => false, 'message' => ''];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  $response['message'] = 'Please log in first.';
  http_response_code(401);
  echo json_encode($response);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $response['message'] = 'Invalid request method.';
  http_response_code(405);
  echo json_encode($response);
  exit();
}

$user_id = (int)$_SESSION['user_id'];
$event_id = (int)($_POST['event_id'] ?? 0);

if ($event_id <= 0) {
  $response['message'] = 'Invalid event ID.';
  http_response_code(400);
  echo json_encode($response);
  exit();
}

try {
  
  // Check if event exists
  $stmt = $pdo->prepare('SELECT id FROM events WHERE id = ?');
  $stmt->execute([$event_id]);
  $event = $stmt->fetch();
  if (!$event) {
    $response['message'] = 'Event not found.';
    http_response_code(404);
    echo json_encode($response);
    exit();
  }
  
  // Check if user already joined
  $stmt = $pdo->prepare('SELECT id FROM event_participation WHERE user_id = ? AND event_id = ?');
  $stmt->execute([$user_id, $event_id]);
  if ($stmt->fetch()) {
    $response['message'] = 'You already joined this event.';
    http_response_code(409);
    echo json_encode($response);
    exit();
  }
  
  // Insert participation record
  $stmt = $pdo->prepare('INSERT INTO event_participation (user_id, event_id) VALUES (?, ?)');
  $stmt->execute([$user_id, $event_id]);
  
  $response['success'] = true;
  $response['message'] = 'Successfully joined the event!';
  http_response_code(200);
  
} catch (Exception $e) {
  $response['message'] = 'Error: ' . $e->getMessage();
  http_response_code(500);
}

echo json_encode($response);
