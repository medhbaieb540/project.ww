
<?php
include '../../Controller/eventController.php';
$eventC = new eventController();
$eventC->deleteevent((int)($_GET['id'] ?? 0));
header('Location: event.php');
exit;
?>


