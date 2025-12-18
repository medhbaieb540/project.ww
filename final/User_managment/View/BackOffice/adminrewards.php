<?php
// View/BackOffice/adminrewards.php  (or wherever your file is)

session_set_cookie_params(['path' => '/FINAL/', 'httponly' => true]);
session_start();

if (!in_array($_SESSION['user_role'] ?? '', ['admin', 'super_admin'], true)) {
  header("Location: ../FrontOffice/login.php");
  exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../Model/Reward.php';


$rewardModel = new Reward($pdo);

$errors = [];
$success = "";

// ✅ CREATE reward
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $title = trim($_POST['title'] ?? '');
    $value = trim($_POST['value'] ?? '');
    $type  = trim($_POST['type'] ?? '');

    if ($title === '') $errors[] = "Title is required.";
    if ($value === '' || !is_numeric($value)) $errors[] = "Value must be a number.";
    if ($type === '') $errors[] = "Type is required.";

    if (empty($errors)) {
        $ok = $rewardModel->create($title, (float)$value, $type);
        if ($ok) {
            $success = "Reward added successfully.";
        } else {
            $errors[] = "Failed to add reward.";
        }
    }
}

// ✅ DELETE reward
if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // optional safety: prevent deleting used reward
    if ($rewardModel->isUsed($id)) {
        $errors[] = "This reward is used in a tournament. Remove it from tournaments first.";
    } else {
        $rewardModel->delete($id);
        $success = "Reward deleted successfully.";
    }
}

// ✅ Always load rewards for display
$rewards = $rewardModel->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GameBridge | Admin Rewards</title>

  <!-- ✅ keep your paths (fix if needed) -->
  <link rel="stylesheet" href="../../public/css/tournaments.css">
  <link rel="stylesheet" href="../../public/css/admin-rewards.css">
</head>
<body>

  <div class="sidebar">
    <h2>Admin</h2>

    <a href="admin.php" class="nav-link">Dashboard</a>
    <a href="admin.php?section=users" class="nav-link">Users</a>
    <a href="games_dashboard.php" class="nav-link">Games</a>
    <a href="admintour.php" class="nav-link">Tournaments</a>
    <a href="adminrewards.php" class="nav-link active">Rewards</a>
    <a href="event/event.php" class="nav-link">Events</a>

    <a href="#" class="nav-link" id="feedbackToggle">Feedback ▾</a>
    <div id="feedbackSubmenu" style="display:none; padding-left:12px;">
      <a href="admin_feedback.php" class="nav-link" style="padding:8px 0;">Dashboard</a>
      <a href="admin_feedback_manage.php" class="nav-link" style="padding:8px 0;">Manage</a>
      <a href="admin_feedback_analytics.php" class="nav-link" style="padding:8px 0;">Analytics</a>
    </div>

    <a href="../FrontOffice/logout.php"
      onclick="return confirm('Are you sure you want to logout?');"
      style="background:#ff4d4d; color:#000; font-weight:700;"
      class="nav-link">
      Logout
    </a>
  </div>

  <div class="main-content">
    <h1>Rewards Management</h1>
    <p class="subtitle">Create and manage rewards that can be attached to tournaments.</p>

    <?php if (!empty($errors)): ?>
      <div class="alert-error">
        <?php foreach ($errors as $err): ?>
          <div>- <?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <h2 class="section-title">Add New Reward</h2>

    <form class="add-form" action="adminrewards.php" method="POST">
      <input type="hidden" name="action" value="create">

      <label for="title">Title</label>
      <input type="text" id="title" name="title" placeholder="e.g. Bronze Pack, Pro Bundle..." required>

      <label for="value">Value</label>
      <input type="number" step="0.01" id="value" name="value" placeholder="e.g. 100" required>

      <label for="type">Type</label>
      <select id="type" name="type" required>
        <option value="">-- Select type --</option>
        <option value="points">Points</option>
        <option value="cash">Cash</option>
        <option value="badge">Badge</option>
        <option value="item">Item</option>
      </select>

      <button type="submit">Add Reward</button>
    </form>

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
                <a class="btn-delete"
                   href="adminrewards.php?action=delete&id=<?= (int)$r['id'] ?>"
                   onclick="return confirm('Delete this reward?');">
                  Delete
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5">No rewards found. Add one above.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <footer>© 2025 GameBridge • Admin Panel</footer>
  </div>

  <script>
    (function() {
      const toggle = document.getElementById('feedbackToggle');
      const submenu = document.getElementById('feedbackSubmenu');
      if (!toggle || !submenu) return;

      toggle.addEventListener('click', function (e) {
        e.preventDefault();
        const isOpen = submenu.style.display === 'block';
        submenu.style.display = isOpen ? 'none' : 'block';
        toggle.textContent = isOpen ? 'Feedback ▾' : 'Feedback ▴';
      });

      const page = (location.pathname.split('/').pop() || '').toLowerCase();
      if (page.includes('admin_feedback')) {
        submenu.style.display = 'block';
        toggle.textContent = 'Feedback ▴';
      }
    })();
  </script>
  <!-- ✅ you had it wrong (you wrote css path). JS must be in /js -->
  <script src="../../public/js/admin-rewards.js"></script>
</body>
</html>
