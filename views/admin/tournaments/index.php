<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GameBridge | Admin Tournaments</title>

 
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="views/admin/tournaments/admin-tournaments.css">
</head>
<body>
  
  <div class="sidebar">
    <h2>Admin</h2>
    <a href="#">Dashboard</a>
    <a href="#">Users</a>
    <a href="#">Games</a>
    <a href="admin_tournaments.php" class="active">Tournaments</a>
    <a href="#">Feedback</a>
    <a href="admin_rewards.php">Rewards</a>
  </div>

  <div class="main-content">
    <h1>Tournaments Management</h1>
    <p class="subtitle">Monitor, create, and manage all GameBridge tournaments.</p>

    <div class="top-controls">
      <div class="top-left">
        <button class="btn-primary" id="openAddModal">+ ADD TOURNAMENT</button>
      </div>
      <div class="top-right">
        <select class="filter-select">
          <option value="all">All Status</option>
          <option value="live">Live</option>
          <option value="upcoming">Upcoming</option>
          <option value="finished">Finished</option>
        </select>
        <input class="search-input" type="text" placeholder="Search by title...">
      </div>
    </div>

    <h2 class="section-title">Tournaments List</h2>

    <table>
      <thead>
        <tr>
          <th>TITLE</th>
          <th>STATUS</th>
          <th>PRIZE</th>
          <th>PLAYERS</th>
          <th>START TIME</th>
          <th>DEVELOPER</th>
          <th>ACTIONS</th>
        </tr>
      </thead>
      <tbody id="tournaments-body">
        <?php if (!empty($tournaments)): ?>
          <?php foreach ($tournaments as $t): ?>
            <?php
              $status = $t['status'];
              $statusClass = 'status-upcoming';
              if ($status === 'live')      $statusClass = 'status-live';
              if ($status === 'finished')  $statusClass = 'status-finished';

              $prizeValue = $t['reward_value'] ?? null;
              $prize = $prizeValue !== null && $prizeValue !== ''
                       ? '$' . $prizeValue
                       : '$0';

              $playersText = '— / —';    
              $developer   = '@DevUnknown';

             
              $startLocal = '';
              if (!empty($t['start_date'])) {
                  $startLocal = str_replace(' ', 'T', substr($t['start_date'], 0, 16));
              }
            ?>
            <tr>
              <td><?= htmlspecialchars($t['name']) ?></td>

              <td>
                <span class="status-pill <?= $statusClass ?>">
                  <?= strtoupper(htmlspecialchars($status)) ?>
                </span>
              </td>

              <td><?= htmlspecialchars($prize) ?></td>

              <td><?= htmlspecialchars($playersText) ?></td>

              <td><?= htmlspecialchars($t['start_date'] ?? '—') ?></td>

              <td><?= htmlspecialchars($developer) ?></td>

              <td class="actions-cell">
                
                <button
                  class="btn-view"
                  data-name="<?= htmlspecialchars($t['name'], ENT_QUOTES) ?>"
                  data-status="<?= htmlspecialchars($status, ENT_QUOTES) ?>"
                  data-prize="<?= htmlspecialchars($prize, ENT_QUOTES) ?>"
                >
                  VIEW
                </button>
                <button
                  class="btn-edit"
                  data-id="<?= (int)$t['id'] ?>"
                  data-name="<?= htmlspecialchars($t['name'], ENT_QUOTES) ?>"
                  data-description="<?= htmlspecialchars($t['description'] ?? '', ENT_QUOTES) ?>"
                  data-reward-id="<?= (int)($t['reward_id'] ?? 0) ?>"
                  data-start="<?= htmlspecialchars($startLocal, ENT_QUOTES) ?>"
                >
                  EDIT
                </button>

                
                <a class="btn-delete"
                   href="admin_tournaments.php?action=delete&id=<?= (int)$t['id'] ?>"
                   onclick="return confirm('Delete this tournament?');">
                  DELETE
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7">No tournaments found in the database.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <p class="table-summary">
      Showing <?= count($tournaments) ?> tournaments 
    </p>

    <footer>© 2025 GameBridge • Admin Panel</footer>
  </div>

  <div class="modal-overlay" id="addModal">
    <div class="modal-content">
      <span class="modal-close" id="closeAddModal">&times;</span>

      <h3>Add Tournament</h3>
      <p class="modal-sub">Select a reward and set the basic details.</p>

      <form action="admin_tournaments.php?action=create" method="POST" class="modal-form">

        <label for="name">Title</label>
        <input type="text" id="name" name="name" placeholder="Tournament name" >

        <label for="reward_id">Reward</label>
        <select id="reward_id" name="reward_id" >
          <option value="">-- Choose a reward --</option>
          <?php foreach ($rewards as $r): ?>
            <option value="<?= (int)$r['id'] ?>">
              <?= htmlspecialchars($r['title']) ?>
              (<?= htmlspecialchars($r['value']) . ' ' . htmlspecialchars($r['type']) ?>)
            </option>
          <?php endforeach; ?>
        </select>

        <label for="start_date">Start Time</label>
        <input type="datetime-local" id="start_date" name="start_date" >

        <label for="description">Description</label>
        <input type="text" id="description" name="description" placeholder="Short description">

        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancelAdd">CANCEL</button>
          <button type="submit" class="btn-save">SAVE</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal-overlay" id="editModal">
    <div class="modal-content">
      <span class="modal-close" id="closeEditModal">&times;</span>

      <h3>Edit Tournament</h3>
      <p class="modal-sub">Update the tournament and save the changes.</p>

      <form action="admin_tournaments.php?action=update" method="POST" class="modal-form">
        
        <input type="hidden" id="edit_id" name="id">

        <label for="edit_name">Title</label>
        <input type="text" id="edit_name" name="name" >

        <label for="edit_reward_id">Reward</label>
        <select id="edit_reward_id" name="reward_id" >
          <option value="">-- Choose a reward --</option>
          <?php foreach ($rewards as $r): ?>
            <option value="<?= (int)$r['id'] ?>">
              <?= htmlspecialchars($r['title']) ?>
              (<?= htmlspecialchars($r['value']) . ' ' . htmlspecialchars($r['type']) ?>)
            </option>
          <?php endforeach; ?>
        </select>

        <label for="edit_start_date">Start Time</label>
        <input type="datetime-local" id="edit_start_date" name="start_date" >

        <label for="edit_description">Description</label>
        <input type="text" id="edit_description" name="description">

        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancelEdit">CANCEL</button>
          <button type="submit" class="btn-save">SAVE CHANGES</button>
        </div>
      </form>
    </div>
  </div>


  <script src="views/admin/tournaments/admin-tournaments.js"></script>

</body>
</html>
