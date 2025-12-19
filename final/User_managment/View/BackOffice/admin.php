<?php
// User_managment/View/BackOffice/admin.php
require_once __DIR__ . '/../../Controller/AdminAction.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GameBridge | Admin Dashboard</title>
  <link rel="stylesheet" href="../../public/css/styleAdmin.css">
  <link rel="stylesheet" href="../../public/css/admin_inline.css">
  
</head>
<body>

  <!-- ===== Sidebar ===== -->
  <div class="sidebar">
    <h2>Admin</h2>

    <a href="#" class="nav-link active" data-section="dashboard">Dashboard</a>
    <a href="#" class="nav-link" data-section="users">Users</a>
    <a href="games_dashboard.php" class="nav-link">Games</a>

    <!-- ✅ GO TO YOUR WORKING GAMEBRIDGE ADMIN PAGES -->
    <a href="admintour.php" class="nav-link">Tournaments</a>
    <a href="adminrewards.php" class="nav-link">Rewards</a>
    <a href="event/event.php" class="nav-link">Events</a>


    <a href="#" class="nav-link" id="feedbackToggle" data-target="feedbackSubmenu">Feedback ▾</a>
    <div id="feedbackSubmenu" style="display:none; padding-left:12px;">
      <a href="admin_feedback.php" class="nav-link" style="padding:8px 0;">Dashboard</a>
      <p></p>
      <p></p>
      <p></p>
      <a href="admin_feedback_manage.php" class="nav-link" style="padding:8px 0;">Manage</a>
      <p></p>
      <p></p>
      <p></p>
      <a href="admin_feedback_analytics.php" class="nav-link" style="padding:8px 0;">Analytics</a>
    </div>

    <a href="../FrontOffice/logout.php"
      onclick="return confirm('Are you sure you want to logout?');"
      style="background:#ff4d4d; color:#000; font-weight:700;">
      Logout
    </a>
  </div>

  <!-- ===== Main Content ===== -->
  <div class="main-content">
    <h1>GameBridge Admin Dashboard</h1>

    <!-- ========== DASHBOARD SECTION (default) ========== -->
    <div id="section-dashboard" class="section <?= $activeSection === 'dashboard' ? 'active' : '' ?>">

      <div class="stats-grid">
        <div class="stat-card">
          <h3>👥 Users</h3>
          <p><?= number_format($userCount) ?> Registered</p>
        </div>
        <div class="stat-card">
          <h3>🎮 Games</h3>
          <p><?= number_format($gameCount) ?> Uploaded</p>
        </div>
        <div class="stat-card">
          <h3>🏆 Tournaments</h3>
          <p><?= number_format($liveToursCount) ?> Total</p>
        </div>
        <div class="stat-card">
          <h3>🐞 Reports</h3>
          <p><?= number_format($pendingFeedbackCount) ?> Total</p>
        </div>
      </div>

      <h2 class="section-title">Manage Users</h2>
      <table>
        <thead>
          <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($previewUsers)): ?>
          <?php foreach ($previewUsers as $u): ?>
            <?php if ($u['user_role'] === 'super_admin') continue; ?>
            <tr>
              <td>@<?= htmlspecialchars($u['username']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($u['user_role']) ?></td>
              <td>
                <a href="admin.php?section=users"><button>View</button></a>
                <a href="delete.php?id=<?= $u['id'] ?>"
                  onclick="return confirm('Delete this user?');">
                  <button class="delete-btn">Delete</button>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="4">No users found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>

      <p style="margin-top:-15px; margin-bottom:40px; color:#888;">
        Showing latest 3 users • Go to <strong>Users</strong> section to manage all users.
      </p>

      <h2 class="section-title">Manage Games</h2>
      <table>
        <thead>
          <tr>
            <th>Title</th>
            <th>Developer</th>
            <th>Category</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($previewGames)): ?>
          <?php foreach ($previewGames as $game): ?>
            <tr>
              <td><?= htmlspecialchars($game['title']) ?></td>
              <td>@<?= htmlspecialchars($game['developer_name'] ?? 'Unknown') ?></td>
              <td><?= htmlspecialchars($game['category_id'] ?? 'N/A') ?></td>
              <td>
                <a href="games_dashboard.php?id=<?= $game['game_id'] ?>"><button>View</button></a>
                <a href="games_dashboard.php?delete=<?= $game['game_id'] ?>" 
                  onclick="return confirm('Delete this game?');">
                  <button class="delete-btn">Delete</button>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="4">No games found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>

      <p style="margin-top:-15px; margin-bottom:40px; color:#888;">
        Showing latest 3 games • Go to <strong>Games</strong> section to manage all games.
      </p>

      <h2 class="section-title">Manage Tournaments</h2>
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Prize Pool</th>
            <th>Max Players</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($previewTournaments)): ?>
          <?php foreach ($previewTournaments as $tournament): ?>
            <tr>
              <td><?= htmlspecialchars($tournament['name'] ?? 'Unknown') ?></td>
              <td>$<?= number_format($tournament['reward_value'] ?? 0) ?></td>
              <td><?= htmlspecialchars($tournament['max_players'] ?? 'N/A') ?></td>
              <td>
                <a href="admintour.php?id=<?= $tournament['id'] ?>"><button>View</button></a>
                <a href="admintour.php?delete=<?= $tournament['id'] ?>" 
                  onclick="return confirm('Delete this tournament?');">
                  <button class="delete-btn">Delete</button>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="4">No tournaments found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>

      <p style="margin-top:-15px; margin-bottom:40px; color:#888;">
        Showing latest 3 tournaments • Go to <strong>Tournaments</strong> section to manage all tournaments.
      </p>

      <h2 class="section-title">Manage Events</h2>
      <table>
        <thead>
          <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Start Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($previewEvents)): ?>
          <?php foreach ($previewEvents as $event): ?>
            <tr>
              <td><?= htmlspecialchars($event['title'] ?? 'Unknown') ?></td>
              <td><?= htmlspecialchars(substr($event['description'] ?? '', 0, 50)) . (strlen($event['description'] ?? '') > 50 ? '...' : '') ?></td>
              <td><?= htmlspecialchars($event['start_date'] ?? 'N/A') ?></td>
              <td>
                <a href="event/event.php?id=<?= $event['id'] ?>"><button>View</button></a>
                <a href="event/event.php?delete=<?= $event['id'] ?>" 
                  onclick="return confirm('Delete this event?');">
                  <button class="delete-btn">Delete</button>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="4">No events found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>

      <p style="margin-top:-15px; margin-bottom:40px; color:#888;">
        Showing latest 3 events • Go to <strong>Events</strong> section to manage all events.
      </p>

      <h2 class="section-title">Manage Feedback</h2>
      <table>
        <thead>
          <tr>
            <th>From User</th>
            <th>Game</th>
            <th>Type</th>
            <th>Message</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($previewFeedback)): ?>
          <?php foreach ($previewFeedback as $fb): ?>
            <tr>
              <td>@<?= htmlspecialchars($fb['username'] ?? 'Unknown') ?></td>
              <td><?= htmlspecialchars($fb['game'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($fb['type'] ?? 'feedback') ?></td>
              <td><?= htmlspecialchars(substr($fb['message'] ?? '', 0, 40)) . (strlen($fb['message'] ?? '') > 40 ? '...' : '') ?></td>
              <td>
                <a href="admin_feedback.php?id=<?= $fb['id'] ?>"><button>View</button></a>
                <a href="admin_feedback_manage.php?delete=<?= $fb['id'] ?>" 
                  onclick="return confirm('Delete this feedback?');">
                  <button class="delete-btn">Delete</button>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5">No feedback found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>

      <p style="margin-top:-15px; margin-bottom:40px; color:#888;">
        Showing latest 3 feedback • Go to <strong>Feedback</strong> section to manage all feedback.
      </p>

      <footer>© 2025 GameBridge • Admin Panel</footer>
    </div>

    <!-- ========== USERS SECTION ========== -->
    <div id="section-users" class="section <?= $activeSection === 'users' ? 'active' : '' ?>">
      <h2 class="section-title">All Users</h2>

      <form method="GET" style="margin-bottom: 20px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <input type="hidden" name="section" value="users">

        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
          placeholder="Search by username or email..."
          style="padding:10px 14px;width:220px;border-radius:8px;border:1px solid #1aff8744;background:#0f0f0f;color:white;outline:none;"
        >

        <select name="role"
          style="padding:9px 10px;border-radius:8px;border:1px solid #1aff8744;background:#0f0f0f;color:white;outline:none;"
        >
          <option value="">All roles</option>
          <option value="player"    <?= ($filterRole === 'player')    ? 'selected' : '' ?>>Player</option>
          <option value="developer" <?= ($filterRole === 'developer') ? 'selected' : '' ?>>Developer</option>
          <option value="admin"     <?= ($filterRole === 'admin')     ? 'selected' : '' ?>>Admin</option>
        </select>

        <select name="status"
          style="padding:9px 10px;border-radius:8px;border:1px solid #1aff8744;background:#0f0f0f;color:white;outline:none;"
        >
          <option value="">All status</option>
          <option value="active" <?= ($filterStatus === 'active') ? 'selected' : '' ?>>Active</option>
          <option value="banned" <?= ($filterStatus === 'banned') ? 'selected' : '' ?>>Banned</option>
        </select>

        <button type="submit"
          style="padding: 8px 14px;border-radius: 6px;background: #1aff87;border:none;cursor:pointer;"
        >
          Apply
        </button>

        <a href="admin.php?section=users" style="font-size:12px; color:#aaa; text-decoration:none;">
          Reset filters
        </a>
      </form>

      <table>
        <thead>
          <tr>
            <th><?= buildSortLink('username', 'Username') ?></th>
            <th><?= buildSortLink('email', 'Email') ?></th>
            <th><?= buildSortLink('user_role', 'Role') ?></th>
            <th><?= buildSortLink('birth_date', 'Birth Date') ?></th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          <?php if (!empty($users)): ?>
            <?php foreach ($users as $u): ?>
              <?php if ($u['user_role'] === 'super_admin') continue; ?>

              <tr>
                <td>@<?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['user_role']) ?></td>
                <td><?= htmlspecialchars($u['birth_date']) ?></td>
                <td>
                  <?php if (!empty($u['is_banned']) && (int)$u['is_banned'] === 1): ?>
                    <span class="status-badge banned">Banned</span>
                  <?php else: ?>
                    <span class="status-badge active">Active</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="update.php?id=<?= $u['id'] ?>">
                    <button>Edit</button>
                  </a>

                  <?php if (empty($u['is_banned']) || (int)$u['is_banned'] === 0): ?>
                    <a href="ban.php?id=<?= $u['id'] ?>"
                      onclick="return confirm('Ban this user?');">
                      <button class="delete-btn">Ban</button>
                    </a>
                  <?php else: ?>
                    <a href="unban.php?id=<?= $u['id'] ?>"
                      onclick="return confirm('Unban this user?');">
                      <button>Unban</button>
                    </a>

                    <a href="delete.php?id=<?= $u['id'] ?>"
                      onclick="return confirm('User is banned. Delete permanently?');">
                      <button class="delete-btn">Delete</button>
                    </a>
                  <?php endif; ?>
                </td>
              </tr>

            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5">No users found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div> <!-- ✅ FIX: close main-content -->

  <script>
    (function() {
      const toggle = document.getElementById('feedbackToggle');
      const submenu = document.getElementById('feedbackSubmenu');
      if (!toggle || !submenu) return;

      // Show submenu when link is clicked
      toggle.addEventListener('click', function (e) {
        e.preventDefault();
        const isOpen = submenu.style.display === 'block';
        submenu.style.display = isOpen ? 'none' : 'block';
        toggle.textContent = isOpen ? 'Feedback ▾' : 'Feedback ▴';
      });

      // Auto-expand when already on a feedback page
      const page = (location.pathname.split('/').pop() || '').toLowerCase();
      if (page.includes('admin_feedback')) {
        submenu.style.display = 'block';
        toggle.textContent = 'Feedback ▴';
      }
    })();
  </script>
  <script src="../../public/js/admin.js?v=2"></script>
</body>
</html>
