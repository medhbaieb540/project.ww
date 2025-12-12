<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/TournamentController.php';

$controller = new TournamentController($pdo);
$controller->handleRequest();
$tournaments   = $controller->getTournaments();
$rewards       = $controller->getRewards();
$pModel        = $controller->getParticipationModel();
$currentUserId = $controller->getCurrentUserId();

function tournament_image(string $name): string {
    $first = strtolower(preg_replace('/\s+.*/', '', $name));
    return "images/tournamentspic/{$first}.jpg";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GameBridge | Tournaments</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
  <div class="logo-container">
    <img src="assets\images\logo.jpg" alt="Logo">
  </div>
  <nav>
    <a href="index.php">Home</a>
    <a href="games.php">Games</a>
    <a href="tournaments.php" class="active">Tournaments</a>
    <a href="community.php">Community</a>
    <a href="users.php">My Profile</a>
    <a href="feedback.php">Feedback</a>
    <a href="rewards.php">Rewards</a>
  </nav>
</header>

<section class="tournaments-section">
  <h2>Active Tournaments</h2>

  <form class="tournament-filters" onsubmit="return false;">
    <input
      type="text"
      id="searchTournaments"
      placeholder="Search tournaments..."
    >

    <select id="statusFilter">
      <option value="all">All statuses</option>
      <option value="live">Live</option>
      <option value="upcoming">Upcoming</option>
      <option value="finished">Finished</option>
    </select>
  </form>

  <div class="dev-controls">
    <button id="openAddModal">+ Add Tournament (Dev)</button>
  </div>

  <div class="tournament-grid">
    <?php foreach ($tournaments as $t): ?>
      <?php
        $status = $t['status'];

        $badgeClass = $status === 'live' ? 'badge-live'
                   : ($status === 'finished' ? 'badge-finished' : 'badge-upcoming');
        $badgeText  = ucfirst($status);

        $prize = $t['reward_value'] !== null ? '$' . $t['reward_value'] : '$0';

        $dataStart = '';
        if (!empty($t['start_date'])) {
            $dataStart = date('Y-m-d\TH:i:s', strtotime($t['start_date']));
        }

        $img = tournament_image($t['name']);

        $maxPlayers     = (int)($t['max_players'] ?? 16);
        $currentPlayers = $pModel->countByTournament((int)$t['id']);
        $isJoined       = $pModel->isUserInTournament((int)$t['id'], $currentUserId);

        $playersText = $currentPlayers . ' / ' . $maxPlayers;
      ?>

      <?php if ($status === 'live'): ?>
        <div class="tournament-card"
             data-id="<?= (int)$t['id'] ?>"
             data-status="live"
             data-start="<?= htmlspecialchars($dataStart) ?>"
             data-joined="<?= $isJoined ? '1' : '0' ?>"
             data-current="<?= $currentPlayers ?>"
             data-max="<?= $maxPlayers ?>">

          <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
          <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($t['name']) ?>">
          <h3><?= htmlspecialchars($t['name']) ?></h3>
          <p class="prize">Prize: <?= htmlspecialchars($prize) ?></p>
          <p class="players">Players: <?= htmlspecialchars($playersText) ?></p>
          <button class="check-btn">Check</button>
        </div>

      <?php elseif ($status === 'upcoming'): ?>
        <div class="tournament-card"
             data-id="<?= (int)$t['id'] ?>"
             data-status="upcoming"
             data-start="<?= htmlspecialchars($dataStart) ?>"
             data-joined="<?= $isJoined ? '1' : '0' ?>"
             data-current="<?= $currentPlayers ?>"
             data-max="<?= $maxPlayers ?>">

          <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
          <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($t['name']) ?>">
          <h3><?= htmlspecialchars($t['name']) ?></h3>
          <p class="prize">Prize: <?= htmlspecialchars($prize) ?></p>
          <p class="countdown">Starts in: --h --m --s</p>
          <p class="players">Players: <?= htmlspecialchars($playersText) ?></p>
          <button class="check-btn">Check</button>
        </div>

      <?php else: ?>
        <div class="tournament-card"
             data-id="<?= (int)$t['id'] ?>"
             data-status="finished"
             data-joined="<?= $isJoined ? '1' : '0' ?>"
             data-current="<?= $currentPlayers ?>"
             data-max="<?= $maxPlayers ?>">

          <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
          <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($t['name']) ?>">
          <h3><?= htmlspecialchars($t['name']) ?></h3>
          <p class="prize">Prize: <?= htmlspecialchars($prize) ?></p>
        </div>
      <?php endif; ?>

    <?php endforeach; ?>
  </div>
</section>

<div id="detailsModal" class="details-modal">
  <div class="details-content">
    <span id="closeDetails" class="details-close">&times;</span>

    <img id="detailsImage" src="" alt="">
    <h3 id="detailsTitle"></h3>
    <p id="detailsStatus"></p>
    <p id="detailsCountdown"></p>
    <p id="detailsPrize"></p>
    <p id="detailsPlayers"></p>

    <div id="detailsActions">
      <button id="joinBtnPopup">Join</button>
      <button id="leaveBtnPopup">Leave</button>
      <button id="spectateBtnPopup">Spectate</button>
    </div>
  </div>
</div>


<div id="addModal" class="details-modal">
  <div class="details-content add-form">
    <span id="closeAdd" class="details-close">&times;</span>
    <h3>Add Tournament (Dev)</h3>

    <form method="POST">
      <input type="hidden" name="dev_add" value="1">

      <label>Title</label>
      <input name="title" type="text" >

      <label>Prize (Reward)</label>
      <select name="reward_id" >
        <option value="">-- Select Reward --</option>
        <?php foreach ($rewards as $r): ?>
          <option value="<?= $r['id'] ?>">
            <?= htmlspecialchars($r['title']) ?> ($<?= $r['value'] ?>)
          </option>
        <?php endforeach; ?>
      </select>

      <label>Start Time</label>
      <input type="datetime-local" name="start" >

      <label>Max Players</label>
      <input type="number" name="max_players" min="2" value="16">

      <button type="submit">Add Tournament</button>
    </form>
  </div>
</div>


<form id="actionForm" method="POST" style="display:none;">
  <input type="hidden" name="action" id="actionType">
  <input type="hidden" name="tournament_id" id="actionTournamentId">
</form>

<script src="assets\js\tournaments.js"></script>
</body>
</html>
