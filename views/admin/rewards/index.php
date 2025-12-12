<?php
// views/admin/rewards/index.php
// Variables from controller:
// $rewards (array), $errors (array), $success (string|null)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GameBridge | Admin Rewards</title>

  <!-- Global CSS -->
  <link rel="stylesheet" href="style.css">
  <!-- Page-specific CSS -->
  <link rel="stylesheet" href="views/admin/rewards/admin-rewards.css">
</head>
<body>

  <!-- ===== Sidebar ===== -->
  <div class="sidebar">
    <h2>Admin</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="#">Users</a>
    <a href="#">Games</a>
    <a href="admin_tournaments.php">Tournaments</a>
    <a href="#">Feedback</a>
    <a href="admin_rewards.php" class="active">Rewards</a>
  </div>

  <!-- ===== Main Content ===== -->
  <div class="main-content">
    <h1>Rewards Management</h1>
    <p class="subtitle">Create and manage rewards that can be attached to tournaments.</p>

    <!-- ===== Alerts (Errors / Success) ===== -->
    <?php if (!empty($errors)): ?>
      <div class="alert-error">
        <?php foreach ($errors as $err): ?>
          <div>- <?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="alert-success">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <!-- ===== Add New Reward (TOP) ===== -->
    <h2 class="section-title">Add New Reward</h2>

    <form class="add-form" action="admin_rewards.php" method="POST">
      <input type="hidden" name="action" value="create">

      <label for="title">Title</label>
      <input
        type="text"
        id="title"
        name="title"
        placeholder="e.g. Bronze Pack, Pro Bundle..."
        
      >

      <label for="value">Value</label>
      <input
        type="number"
        step="0.01"
        id="value"
        name="value"
        placeholder="e.g. 100"
        
      >

      <label for="type">Type</label>
      <select id="type" name="type" >
        <option value="">-- Select type --</option>
        <option value="points">Points</option>
        <option value="cash">Cash</option>
        <option value="badge">Badge</option>
        <option value="item">Item</option>
      </select>

      <button type="submit">Add Reward</button>
    </form>

    <!-- ===== Existing Rewards Table ===== -->
    <h2 class="section-title">Existing Rewards</h2>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Value</th>
          <th>Type</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($rewards)): ?>
          <?php foreach ($rewards as $r): ?>
            <tr>
              <td><?= (int)$r['id'] ?></td>
              <td><?= htmlspecialchars($r['title']) ?></td>
              <td><?= htmlspecialchars($r['value']) ?></td>
              <td><?= htmlspecialchars($r['type']) ?></td>
              <td>
                <a
                  class="btn-delete"
                  href="admin_rewards.php?action=delete&id=<?= (int)$r['id'] ?>"
                  onclick="return confirm('Delete this reward?');"
                >
                  Delete
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5">No rewards found. Add one above.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <footer>© 2025 GameBridge • Admin Panel</footer>
  </div>
  <script src="assets/js/admin-rewards.js"></script>

</body>
</html>
