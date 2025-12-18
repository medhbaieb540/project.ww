<?php
// View/BackOffice/admintour.php  ✅ single-file entry like adminrewards.php

session_set_cookie_params(['path' => '/FINAL/', 'httponly' => true]);
session_start();

if (!in_array($_SESSION['user_role'] ?? '', ['admin', 'super_admin'], true)) {
  header("Location: ../FrontOffice/login.php");
  exit;
}

require_once __DIR__ . '/../../config/db.php';

$errors  = [];
$success = "";

// ---------- helpers ----------
function uploadTournamentImage(string $fieldName): ?string
{
    if (empty($_FILES[$fieldName]) || ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    $tmpName  = $_FILES[$fieldName]['tmp_name'];
    $origName = basename($_FILES[$fieldName]['name']);

    // Save inside: User_managment/assets/images/tournaments/
    $uploadDirFs  = __DIR__ . '/../../assets/images/tournaments/';
    $uploadDirUrl = 'assets/images/tournaments/';

    if (!is_dir($uploadDirFs)) {
        @mkdir($uploadDirFs, 0777, true);
    }

    $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $origName);
    $targetFs = $uploadDirFs . $safeName;

    if (@move_uploaded_file($tmpName, $targetFs)) {
        return $uploadDirUrl . $safeName;
    }

    return null;
}

// ---------- ACTIONS ----------
$action = $_GET['action'] ?? '';

// ✅ DELETE
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    if ($id <= 0) {
        $errors[] = "Invalid tournament id.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM tournaments WHERE id = ?");
            $ok = $stmt->execute([$id]);

            if ($ok) $success = "Tournament deleted.";
            else     $errors[] = "Failed to delete tournament.";
        } catch (Exception $e) {
            $errors[] = "Delete failed (DB constraint maybe).";
        }
    }
}

// ✅ CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $startDate   = $_POST['start_date'] ?? '';
    $endDate     = $_POST['end_date'] ?? '';
    $rewardId    = (int)($_POST['reward_id'] ?? 0);

    if ($name === '') $errors[] = "Title is required.";
    if ($rewardId <= 0) $errors[] = "Reward is required.";
    if ($startDate === '') $errors[] = "Start time is required.";
    if ($endDate === '') $errors[] = "End time is required.";
    if ($startDate && $endDate && strtotime($endDate) <= strtotime($startDate)) {
        $errors[] = "End time must be AFTER start time.";
    }

    if (empty($errors)) {
        $imagePath = uploadTournamentImage('image');

        try {
            $sql = "INSERT INTO tournaments (name, description, start_date, end_date, reward_id, max_players, image_path)
                    VALUES (:name, :description, :start_date, :end_date, :reward_id, :max_players, :image_path)";
            $stmt = $pdo->prepare($sql);

            $ok = $stmt->execute([
                ':name'        => $name,
                ':description' => $description,
                ':start_date'  => $startDate,
                ':end_date'    => $endDate,
                ':reward_id'   => $rewardId,
                ':max_players' => 16,
                ':image_path'  => $imagePath,
            ]);

            if ($ok) $success = "Tournament created.";
            else     $errors[] = "Failed to create tournament.";
        } catch (Exception $e) {
            $errors[] = "Create failed (check DB columns).";
        }
    }
}

// ✅ UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $id          = (int)($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $startDate   = $_POST['start_date'] ?? '';
    $endDate     = $_POST['end_date'] ?? '';
    $rewardId    = (int)($_POST['reward_id'] ?? 0);

    if ($id <= 0) $errors[] = "Invalid tournament id.";
    if ($name === '') $errors[] = "Title is required.";
    if ($rewardId <= 0) $errors[] = "Reward is required.";
    if ($startDate === '') $errors[] = "Start time is required.";
    if ($endDate === '') $errors[] = "End time is required.";
    if ($startDate && $endDate && strtotime($endDate) <= strtotime($startDate)) {
        $errors[] = "End time must be AFTER start time.";
    }

    if (empty($errors)) {
        $imagePath = uploadTournamentImage('image');

        try {
            if ($imagePath !== null) {
                $sql = "UPDATE tournaments
                        SET name=:name, description=:description, start_date=:start_date, end_date=:end_date,
                            reward_id=:reward_id, max_players=:max_players, image_path=:image_path
                        WHERE id=:id";
                $params = [
                    ':name'        => $name,
                    ':description' => $description,
                    ':start_date'  => $startDate,
                    ':end_date'    => $endDate,
                    ':reward_id'   => $rewardId,
                    ':max_players' => 16,
                    ':image_path'  => $imagePath,
                    ':id'          => $id,
                ];
            } else {
                $sql = "UPDATE tournaments
                        SET name=:name, description=:description, start_date=:start_date, end_date=:end_date,
                            reward_id=:reward_id, max_players=:max_players
                        WHERE id=:id";
                $params = [
                    ':name'        => $name,
                    ':description' => $description,
                    ':start_date'  => $startDate,
                    ':end_date'    => $endDate,
                    ':reward_id'   => $rewardId,
                    ':max_players' => 16,
                    ':id'          => $id,
                ];
            }

            $stmt = $pdo->prepare($sql);
            $ok = $stmt->execute($params);

            if ($ok) $success = "Tournament updated.";
            else     $errors[] = "Failed to update tournament.";
        } catch (Exception $e) {
            $errors[] = "Update failed (check DB columns).";
        }
    }
}

// ---------- LOAD DATA FOR VIEW ----------
try {
    $rewardsStmt = $pdo->query("SELECT id, title, value, type FROM rewards ORDER BY id ASC");
    $rewards = $rewardsStmt->fetchAll(PDO::FETCH_ASSOC);

    $tStmt = $pdo->query("
        SELECT
            t.*,
            r.value AS reward_value,
            r.title AS reward_title,
            r.type  AS reward_type
        FROM tournaments t
        LEFT JOIN rewards r ON r.id = t.reward_id
        ORDER BY t.id DESC
    ");
    $tournaments = $tStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $rewards = [];
    $tournaments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GameBridge | Admin Tournaments</title>
  <link rel="stylesheet" href="../../public/css/tournaments.css">
  <link rel="stylesheet" href="../../public/css/admin-tournaments.css">
</head>
<body>

  <div class="sidebar">
    <h2>Admin</h2>

    <a href="admin.php" class="nav-link">Dashboard</a>
    <a href="admin.php?section=users" class="nav-link">Users</a>
    <a href="games_dashboard.php" class="nav-link">Games</a>
    <a href="admintour.php" class="nav-link active">Tournaments</a>
    <a href="adminrewards.php" class="nav-link">Rewards</a>
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
    <h1>Tournaments Management</h1>
    <p class="subtitle">Monitor, create, and manage all GameBridge tournaments.</p>

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

    <div class="top-controls">
      <div class="top-left">
        <button class="btn-primary" id="openAddModal" type="button">+ ADD TOURNAMENT</button>
      </div>
      <div class="top-right">
        <select class="filter-select" id="statusFilterAdmin">
          <option value="all">All Status</option>
          <option value="live">Live</option>
          <option value="upcoming">Upcoming</option>
          <option value="finished">Finished</option>
        </select>
        <input class="search-input" id="searchAdmin" type="text" placeholder="Search by title...">
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
          <th>START</th>
          <th>END</th>
          <th>ACTIONS</th>
        </tr>
      </thead>

      <tbody id="tournaments-body">
        <?php if (!empty($tournaments)): ?>
          <?php foreach ($tournaments as $t): ?>
            <?php
              $status = $t['status'] ?? 'upcoming';
              $statusClass = 'status-upcoming';
              if ($status === 'live')      $statusClass = 'status-live';
              if ($status === 'finished')  $statusClass = 'status-finished';

              $prizeValue = $t['reward_value'] ?? null;
              $prize = ($prizeValue !== null && $prizeValue !== '') ? '$' . $prizeValue : '$0';

              $startLocal = '';
              if (!empty($t['start_date'])) $startLocal = str_replace(' ', 'T', substr($t['start_date'], 0, 16));
              $endLocal = '';
              if (!empty($t['end_date']))   $endLocal   = str_replace(' ', 'T', substr($t['end_date'], 0, 16));
              $img = $t['image_path'] ?? '';
            ?>
            <tr data-status="<?= htmlspecialchars($status) ?>" data-title="<?= htmlspecialchars(strtolower($t['name'])) ?>">
              <td><?= htmlspecialchars($t['name']) ?></td>
              <td>
                <span class="status-pill <?= $statusClass ?>">
                  <?= strtoupper(htmlspecialchars($status)) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($prize) ?></td>
              <td>— / —</td>
              <td><?= htmlspecialchars($t['start_date'] ?? '—') ?></td>
              <td><?= htmlspecialchars($t['end_date'] ?? '—') ?></td>

              <td class="actions-cell">
                <button class="btn-view" type="button">VIEW</button>

                <button
                  class="btn-edit"
                  type="button"
                  data-id="<?= (int)$t['id'] ?>"
                  data-name="<?= htmlspecialchars($t['name'], ENT_QUOTES) ?>"
                  data-description="<?= htmlspecialchars($t['description'] ?? '', ENT_QUOTES) ?>"
                  data-reward-id="<?= (int)($t['reward_id'] ?? 0) ?>"
                  data-start="<?= htmlspecialchars($startLocal, ENT_QUOTES) ?>"
                  data-end="<?= htmlspecialchars($endLocal, ENT_QUOTES) ?>"
                  data-image="<?= htmlspecialchars($img, ENT_QUOTES) ?>"
                >EDIT</button>

                <a class="btn-delete"
                   href="admintour.php?action=delete&id=<?= (int)$t['id'] ?>"
                   onclick="return confirm('Delete this tournament?');">
                  DELETE
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="7">No tournaments found in the database.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <p class="table-summary">Showing <?= count($tournaments) ?> tournaments</p>

    <footer>© 2025 GameBridge • Admin Panel</footer>
  </div>

  <!-- ===== ADD MODAL ===== -->
  <div class="modal-overlay" id="addModal">
    <div class="modal-content">
      <span class="modal-close" id="closeAddModal">&times;</span>

      <h3>Add Tournament</h3>
      <p class="modal-sub">Select a reward and set the basic details.</p>

      <form action="admintour.php?action=create" method="POST" class="modal-form" enctype="multipart/form-data">
        <label for="name">Title</label>
        <input type="text" id="name" name="name" placeholder="Tournament name" required>

        <label for="reward_id">Reward</label>
        <select id="reward_id" name="reward_id" required>
          <option value="">-- Choose a reward --</option>
          <?php foreach ($rewards as $r): ?>
            <option value="<?= (int)$r['id'] ?>">
              <?= htmlspecialchars($r['title']) ?> (<?= htmlspecialchars($r['value']) ?> <?= htmlspecialchars($r['type']) ?>)
            </option>
          <?php endforeach; ?>
        </select>

        <label for="start_date">Start Time</label>
        <input type="datetime-local" id="start_date" name="start_date" required>

        <label for="end_date">End Time</label>
        <input type="datetime-local" id="end_date" name="end_date" required>

        <label for="image">Image</label>
        <input type="file" id="image" name="image" accept="image/*">

        <label for="description">Description</label>
        <input type="text" id="description" name="description" placeholder="Short description">

        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancelAdd">CANCEL</button>
          <button type="submit" class="btn-save">SAVE</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ===== EDIT MODAL ===== -->
  <div class="modal-overlay" id="editModal">
    <div class="modal-content">
      <span class="modal-close" id="closeEditModal">&times;</span>

      <h3>Edit Tournament</h3>
      <p class="modal-sub">Update the tournament and save the changes.</p>

      <form action="admintour.php?action=update" method="POST" class="modal-form" enctype="multipart/form-data">
        <input type="hidden" id="edit_id" name="id">

        <label for="edit_name">Title</label>
        <input type="text" id="edit_name" name="name" required>

        <label for="edit_reward_id">Reward</label>
        <select id="edit_reward_id" name="reward_id" required>
          <option value="">-- Choose a reward --</option>
          <?php foreach ($rewards as $r): ?>
            <option value="<?= (int)$r['id'] ?>">
              <?= htmlspecialchars($r['title']) ?> (<?= htmlspecialchars($r['value']) ?> <?= htmlspecialchars($r['type']) ?>)
            </option>
          <?php endforeach; ?>
        </select>

        <label for="edit_start_date">Start Time</label>
        <input type="datetime-local" id="edit_start_date" name="start_date" required>

        <label for="edit_end_date">End Time</label>
        <input type="datetime-local" id="edit_end_date" name="end_date" required>

        <label for="edit_image">Change Image (optional)</label>
        <input type="file" id="edit_image" name="image" accept="image/*">

        <label for="edit_description">Description</label>
        <input type="text" id="edit_description" name="description">

        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancelEdit">CANCEL</button>
          <button type="submit" class="btn-save">SAVE CHANGES</button>
        </div>
      </form>
    </div>
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

  <script src="../../public/js/admin-tournaments.js"></script>
</body>
</html>
