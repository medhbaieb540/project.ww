<?php

include __DIR__ . '/../../Controller/eventController.php';
require_once __DIR__ . '/../../Model/event.php';



$controller = new eventController();
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: event.php');
    exit;
}

$event = $controller->showevent($id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Show Event</title>
</head>
<body>
    <?php if ($event): ?>
        <h1>Event Details (ID: <?php echo htmlspecialchars($event['id']); ?>)</h1>
        <p><strong>Title:</strong> <?php echo htmlspecialchars($event['title']); ?></p>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
        <p><strong>Organizer ID:</strong> <?php echo htmlspecialchars($event['organizer_id']); ?></p>
        <p><strong>Event Date:</strong> <?php echo htmlspecialchars($event['eventdate']); ?></p>
        <p>
            <a href="updateEvent.php?id=<?php echo urlencode($event['id']); ?>">Edit</a> |
            <a href="deleteEvent.php?id=<?php echo urlencode($event['id']); ?>" onclick="return confirm('Delete this event?')">Delete</a> |
            <a href="event.php">Back to list</a>
        </p>
    <?php else: ?>
        <p>Event not found.</p>
        <p><a href="event.php">Back to list</a></p>
    <?php endif; ?>
</body>
</html>
?>