<?php
require_once _DIR_ . '/../../Controller/eventController.php';
require_once _DIR_ . '/../../Model/event.php';

$message = '';
$old = [
    'id' => '',
    'title' => '',
    'description' => '',
    'organizer_id' => '',
    'eventdate' => ''
];

$eventC = new eventController();

// If form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['id'] = trim($_POST['id'] ?? '');
    $old['title'] = trim($_POST['title'] ?? '');
    $old['description'] = trim($_POST['description'] ?? '');
    $old['organizer_id'] = trim($_POST['organizer_id'] ?? '');
    $old['eventdate'] = trim($_POST['eventdate'] ?? '');

    // Basic validation
    if ($old['id'] === '' || $old['title'] === '' || $old['description'] === '' || $old['organizer_id'] === '' || $old['eventdate'] === '') {
        $message = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!ctype_digit((string)$old['id']) || !ctype_digit((string)$old['organizer_id'])) {
        $message = 'ID et Organizer ID doivent être des nombres entiers.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $old['eventdate'])) {
        $message = 'Le format de la date est invalide (YYYY-MM-DD).';
    } else {
        // parse date
        try {
            $evtDate = new DateTime($old['eventdate']);
        } catch (Exception $e) {
            $evtDate = null;
        }

        // create event object (preserve parameter order)
        $event = new event(
            (int)$old['id'],
            $old['title'],
            $old['description'],
            (int)$old['organizer_id'],
            $evtDate
        );

        // call controller update method (keeps same params as original)
        $res = $eventC->updatevent($event, (int)$old['id']);
        if (is_array($res) ? ($res['ok'] ?? false) : $res) {
            header('Location: event.php');
            exit;
        } else {
            $message = 'Échec de la mise à jour: ' . (is_array($res) ? ($res['msg'] ?? 'inconnu') : 'inconnu');
        }
    }
} else {
    // load for editing by id from GET
    $idToLoad = (int)($_GET['id'] ?? 0);
    if ($idToLoad > 0) {
        $row = $eventC->showevent($idToLoad);
        if ($row) {
            $old['id'] = $row['id'] ?? $idToLoad;
            $old['title'] = $row['title'] ?? '';
            $old['description'] = $row['description'] ?? '';
            $old['organizer_id'] = $row['organizer_id'] ?? '';
            // try to normalize date format
            $old['eventdate'] = isset($row['eventdate']) ? substr($row['eventdate'], 0, 10) : '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'événement</title>
    <style>
        label { display:block; margin-top:8px; font-weight:600; }
        input, textarea { width:100%; max-width:480px; padding:8px; margin-top:4px; }
        .error { color:#a33; margin-bottom:12px; }
        .actions { margin-top:12px; }
    </style>
</head>
<body>
    <?php if ($message): ?>
        <div class="error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="id">ID</label>
        <input type="text" id="id" readonly name="id" value="<?php echo htmlspecialchars($old['id']); ?>">

        <label for="title">Titre</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($old['title']); ?>">

        <label for="description">Description</label>
        <textarea id="description" name="description"><?php echo htmlspecialchars($old['description']); ?></textarea>

        <label for="organizer_id">Organizer ID</label>
        <input type="number" id="organizer_id" name="organizer_id" min="1" value="<?php echo htmlspecialchars($old['organizer_id']); ?>">

        <label for="eventdate">Date de l'événement</label>
        <input type="date" id="eventdate" name="eventdate" value="<?php echo htmlspecialchars($old['eventdate']); ?>">

        <div class="actions">
            <button type="submit">Mettre à jour</button>
            <a href="event.php">Retour</a>
        </div>
    </form>
</body>
</html>