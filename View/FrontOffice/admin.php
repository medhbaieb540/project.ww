<?php
session_start();
require_once '../../controller/UserController.php';

$userC  = new UserController();
$users  = $userC->listUsers();
$activeSection = $_GET['section'] ?? 'dashboard';

// بحث
$search = trim($_GET['search'] ?? '');

// فلترة
$filterRole   = $_GET['role']   ?? '';  // player / developer / admin ...
$filterStatus = $_GET['status'] ?? '';  // active / banned

// الترتيب
$sortField = $_GET['sort'] ?? null;     // username / email / birth_date / user_role
$sortDir   = $_GET['dir']  ?? 'ASC';    // ASC أو DESC

/*
 * 2) نجيب المستخدمين من الداتابيس حسب (search + filter + sort)
 *    دالة جديدة في UserController
 */
$users = $userC->listUsersAdvanced(
    $search,
    $filterRole,
    $filterStatus,
    $sortField,
    $sortDir
);

// للـ Dashboard (آخر 3 حسب نفس الترتيب)
$previewUsers = array_slice($users, 0, 3);

/*
 * 3) Helper لبناء لينكات الترتيب في رأس الجدول
 */
function buildSortLink($column, $label)
{
    $currentSort  = $_GET['sort']   ?? '';
    $currentDir   = $_GET['dir']    ?? 'ASC';
    $search       = $_GET['search'] ?? '';
    $role         = $_GET['role']   ?? '';
    $status       = $_GET['status'] ?? '';

    // لو نفس العمود -> نقلب الاتجاه
    $newDir = 'ASC';
    if ($currentSort === $column && $currentDir === 'ASC') {
        $newDir = 'DESC';
    }

    // نضمن دايمًا section=users عشان ما يرجع للـ dashboard
    $url = "admin.php?section=users&sort=$column&dir=$newDir";

    if ($search !== '') {
        $url .= "&search=" . urlencode($search);
    }
    if ($role !== '') {
        $url .= "&role=" . urlencode($role);
    }
    if ($status !== '') {
        $url .= "&status=" . urlencode($status);
    }

    return "<a href=\"$url\" style=\"color:#1aff87; text-decoration:none;\">$label</a>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GameBridge | Admin Dashboard</title>
  <link rel="stylesheet" href="../../public/css/styleAdmin.css">
  <style>
    body {
      background: #0c0c0c;
      color: var(--text);
      font-family: 'Poppins', sans-serif;
      margin: 0;
      display: flex;
      min-height: 100vh;
    }

    .sidebar {
      width: 250px;
      background: #0f0f0f;
      border-right: 1px solid #1aff8715;
      display: flex;
      flex-direction: column;
      padding: 30px 20px;
    }

    .sidebar h2 {
      font-family: 'Orbitron', sans-serif;
      color: var(--accent);
      margin-bottom: 40px;
      text-align: center;
    }

    .sidebar a {
      color: #bbb;
      text-decoration: none;
      padding: 10px 15px;
      margin-bottom: 10px;
      border-radius: 6px;
      transition: 0.3s;
      font-weight: 500;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background: var(--accent);
      color: #000;
    }

    .main-content {
      flex: 1;
      padding: 40px 60px;
      overflow-y: auto;
    }

    .main-content h1 {
      font-family: 'Orbitron', sans-serif;
      color: var(--accent);
      margin-bottom: 40px;
      text-align: center;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 25px;
      margin-bottom: 60px;
    }

    .stat-card {
      background: var(--bg-card);
      border: 1px solid #1aff8720;
      border-radius: 12px;
      padding: 25px;
      text-align: center;
      transition: 0.3s;
      box-shadow: 0 0 20px #00000055;
    }
    

    .stat-card:hover {
      border-color: var(--accent);
      box-shadow: 0 0 25px #1aff8733;
      transform: translateY(-4px);
    }

    .stat-card h3 {
      font-family: 'Orbitron', sans-serif;
      color: var(--accent);
      font-size: 1.3rem;
      margin-bottom: 8px;
    }

    .stat-card p {
      color: #ccc;
      font-size: 1rem;
    }
      /* Grid for user cards */
.games-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 30px;
}

.game-card {
  background: var(--bg-card);
  border: 1px solid #1aff8722;
  border-radius: 12px;
  padding: 20px;
  transition: 0.3s;
}

.game-card:hover {
  border-color: var(--accent);
  box-shadow: 0 0 20px #1aff8733;
  transform: translateY(-5px);
}

.card-btn {
  padding: 6px 10px;
  border-radius: 6px;
  font-size: 0.9rem;
  margin-right: 10px;
  cursor: pointer;
}

.update-btn {
  border: 2px solid var(--accent);
  color: var(--accent);
  background: transparent;
}

.delete-btn {
  border: 2px solid #ff4d4d;
  color: #ff4d4d;
  background: transparent;
}


    h2.section-title {
      font-family: 'Orbitron', sans-serif;
      color: var(--accent);
      margin-bottom: 15px;
      border-left: 4px solid var(--accent);
      padding-left: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: var(--bg-card);
      border-radius: 10px;
      overflow: hidden;
      margin-bottom: 40px;
    }

    th, td {
      padding: 14px 18px;
      text-align: left;
      font-size: 0.9rem;
    }

    th {
      background: #111;
      color: var(--accent);
      text-transform: uppercase;
      letter-spacing: 1px;
      border-bottom: 1px solid #1aff8711;
    }

    tr:nth-child(even) { background: #121212; }
    tr:hover           { background: #1aff870f; }

    td button {
      background: transparent;
      border: 2px solid var(--accent);
      color: var(--accent);
      border-radius: 6px;
      padding: 4px 10px;
      font-size: 0.8rem;
      cursor: pointer;
      transition: 0.3s;
      text-transform: uppercase;
    }

    td button:hover {
      background: var(--accent);
      color: #000;
    }

    .delete-btn {
      border-color: #ff4d4d;
      color: #ff4d4d;
    }
    .delete-btn:hover {
      background: #ff4d4d;
      color: #000;
    }

    footer {
      text-align: center;
      color: #777;
      font-size: 0.85rem;
      margin-top: 50px;
      border-top: 1px solid #1aff8711;
      padding-top: 15px;
    }

    @media (max-width: 900px) {
      .sidebar { display: none; }
      body { flex-direction: column; }
      .main-content { padding: 20px; }
    }

    /* sections switching */
    .section { display: none; }
    .section.active { display: block; }
  </style>
</head>
<body>

  <!-- ===== Sidebar ===== -->
  <div class="sidebar">
    <h2>Admin</h2>
    <a href="#" class="nav-link active" data-section="dashboard">Dashboard</a>
    <a href="#" class="nav-link" data-section="users">Users</a>
    <a href="#" class="nav-link" data-section="games">Games</a>
    <a href="#" class="nav-link" data-section="tournaments">Tournaments</a>
    <a href="#" class="nav-link" data-section="feedback">Feedback</a>
    <a href="#" class="nav-link" data-section="rewards">Rewards</a>
  </div>

  <!-- ===== Main Content ===== -->
  <div class="main-content">
    <h1>GameBridge Admin Dashboard</h1>

    <!-- ========== DASHBOARD SECTION (default) ========== -->
    <div id="section-dashboard" class="section <?= $activeSection === 'dashboard' ? 'active' : '' ?>">

      <!-- Stats Overview -->
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

      <!-- Preview Users (latest 3) -->
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
                <a href="../BackOffice/update.php?id=<?= $u['id'] ?>">
                  <button>Edit</button>
                </a>
                <a href="../BackOffice/delete.php?id=<?= $u['id'] ?>"
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

      <!-- Sample games (same as قبل) -->
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

 <!-- ========== USERS SECTION (all users) ========== -->
<div id="section-users" class="section <?= $activeSection === 'users' ? 'active' : '' ?>">
  <h2 class="section-title">All Users</h2>
 <!-- 🔍 Search + Filter Bar -->
  <form method="GET" style="margin-bottom: 20px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">

    <!-- نثبت إننا في users section -->
    <input type="hidden" name="section" value="users">

    <!-- Search -->
    <input
      type="text"
      name="search"
      value="<?= htmlspecialchars($search) ?>"
      placeholder="Search by username or email..."
      style="
        padding:10px 14px;
        width:220px;
        border-radius:8px;
        border:1px solid #1aff8744;
        background:#0f0f0f;
        color:white;
        outline:none;
      "
    >

    <!-- Filter by role -->
    <select
      name="role"
      style="
        padding:9px 10px;
        border-radius:8px;
        border:1px solid #1aff8744;
        background:#0f0f0f;
        color:white;
        outline:none;
      "
    >
      <option value="">All roles</option>
      <option value="player"    <?= ($filterRole === 'player')    ? 'selected' : '' ?>>Player</option>
      <option value="developer" <?= ($filterRole === 'developer') ? 'selected' : '' ?>>Developer</option>
      <option value="admin"     <?= ($filterRole === 'admin')     ? 'selected' : '' ?>>Admin</option>
    </select>

    <!-- Filter by status -->
    <select
      name="status"
      style="
        padding:9px 10px;
        border-radius:8px;
        border:1px solid #1aff8744;
        background:#0f0f0f;
        color:white;
        outline:none;
      "
    >
      <option value="">All status</option>
      <option value="active" <?= ($filterStatus === 'active') ? 'selected' : '' ?>>Active</option>
      <option value="banned" <?= ($filterStatus === 'banned') ? 'selected' : '' ?>>Banned</option>
    </select>

    <!-- Filter button -->
    <button
      type="submit"
      style="
        padding: 8px 14px;
        border-radius: 6px;
        background: #1aff87;
        border:none;
        cursor:pointer;
      "
    >
      Apply
    </button>

    <!-- Reset -->
    <a href="admin.php?section=users"
       style="font-size:12px; color:#aaa; text-decoration:none;">
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
          <?php if ($u['user_role'] === 'super_admin') continue; // hide super admin ?>

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
              <a href="../BackOffice/update.php?id=<?= $u['id'] ?>">
                <button>Edit</button>
              </a>
              <?php if (empty($u['is_banned']) || (int)$u['is_banned'] === 0): ?>
                <!-- مش محظور => Ban فقط -->
                <a href="../BackOffice/ban.php?id=<?= $u['id'] ?>"
                   onclick="return confirm('Ban this user?');">
                  <button class="delete-btn">Ban</button>
                </a>
              <?php else: ?>
                <!-- محظور => Unban + Delete -->
                <a href="../BackOffice/unban.php?id=<?= $u['id'] ?>"
                   onclick="return confirm('Unban this user?');">
                  <button>Unban</button>
                </a>

                <a href="../BackOffice/delete.php?id=<?= $u['id'] ?>"
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

    <!-- ========== GAMES SECTION ========== -->
    <div id="section-games" class="section">
      <h2 class="section-title">All Games</h2>
      <p style="color:#aaa;">(لاحقًا ممكن نربطها بجدول الألعاب في الداتابيس)</p>
      <!-- حاليًا نفس الجدول التجريبي -->
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
        </tbody>
      </table>
    </div>

    <!-- ========== TOURNAMENTS SECTION ========== -->
    <div id="section-tournaments" class="section">
      <h2 class="section-title">Tournaments</h2>
      <p style="color:#aaa;">هنا تحط جدول البطولات لاحقًا.</p>
    </div>

    <!-- ========== FEEDBACK SECTION ========== -->
    <div id="section-feedback" class="section">
      <h2 class="section-title">Feedback</h2>
      <p style="color:#aaa;">مكان عرض تعليقات المستخدمين / الريفيوز.</p>
    </div>

    <!-- ========== REWARDS SECTION ========== -->
    <div id="section-rewards" class="section">
      <h2 class="section-title">Rewards</h2>
      <p style="color:#aaa;">قسم الجوائز والنقاط والـ loyalty system.</p>
    </div>

  </div><!-- /main-content -->

  <script src = "../../public/js/admin.js?v=2"></script>

</body>
</html>
