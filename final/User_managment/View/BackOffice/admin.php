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
          <p>1,245 Registered</p>
        </div>
        <div class="stat-card">
          <h3>🎮 Games</h3>
          <p>312 Uploaded</p>
        </div>
        <div class="stat-card">
          <h3>🏆 Tournaments</h3>
          <p>48 Active</p>
        </div>
        <div class="stat-card">
          <h3>🐞 Reports</h3>
          <p>27 Pending</p>
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
                <a href="update.php?id=<?= $u['id'] ?>"><button>Edit</button></a>
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
            <th>Plays</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Neon Runner</td>
            <td>@DevFox</td>
            <td>8,912</td>
            <td><button>Delete</button></td>
          </tr>
          <tr>
            <td>Dead Curse</td>
            <td>@AceGamer</td>
            <td>5,403</td>
            <td><button>Delete</button></td>
          </tr>
          <tr>
            <td>Memory Quest</td>
            <td>@LunaDev</td>
            <td>12,070</td>
            <td><button>Delete</button></td>
          </tr>
        </tbody>
      </table>

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
