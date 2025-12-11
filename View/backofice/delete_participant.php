<?php
// delete_participant.php - Handle participant removal from event
header('Content-Type: application/json');
include __DIR__ . '/../../config.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $response['message'] = 'Invalid request method.';
  http_response_code(405);
  echo json_encode($response);
  exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$participation_id = (int)($input['participation_id'] ?? 0);

if ($participation_id <= 0) {
  $response['message'] = 'Invalid participation ID.';
  http_response_code(400);
  echo json_encode($response);
  exit();
}

try {
  $pdo = config::getConnexion();
  
  // Check if participation exists
  $stmt = $pdo->prepare('SELECT id FROM event_participation WHERE id = ?');
  $stmt->execute([$participation_id]);
  if (!$stmt->fetch()) {
    $response['message'] = 'Participation record not found.';
    http_response_code(404);
    echo json_encode($response);
    exit();
  }
  
  // Delete participation record
  $stmt = $pdo->prepare('DELETE FROM event_participation WHERE id = ?');
  $stmt->execute([$participation_id]);
  
  $response['success'] = true;
  $response['message'] = 'Participant removed successfully!';
  http_response_code(200);
  
} catch (Exception $e) {
  $response['message'] = 'Error: ' . $e->getMessage();
  http_response_code(500);
}

echo json_encode($response);
?>
