<?php
require_once '../../controller/UserController.php';

$userC = new UserController();
$users = $userC->listUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GameBridge | Admin Dashboard</title>
  <link rel="stylesheet" href="../Front Office/styleAdmin.css">
  <style>
    /* ===== ADMIN DASHBOARD ===== */
    body {
      background: #0c0c0c;
      color: var(--text);
      font-family: 'Poppins', sans-serif;
      margin: 0;
      display: flex;
      min-height: 100vh;
    }

    /* ===== Sidebar ===== */
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

    .sidebar a:hover, .sidebar a.active {
      background: var(--accent);
      color: #000;
    }

    /* ===== Main Content ===== */
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

    /* ===== Stats Grid ===== */
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

    /* ===== Tables ===== */
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

    tr:nth-child(even) {
      background: #121212;
    }

    tr:hover {
      background: #1aff870f;
    }

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

    /* ===== Footer ===== */
    footer {
      text-align: center;
      color: #777;
      font-size: 0.85rem;
      margin-top: 50px;
      border-top: 1px solid #1aff8711;
      padding-top: 15px;
    }

    @media (max-width: 900px) {
      .sidebar {
        display: none;
      }
      body {
        flex-direction: column;
      }
      .main-content {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <!-- ===== Sidebar ===== -->
  <div class="sidebar">
    <h2>Admin</h2>
    <a href="#" class="active">Dashboard</a>
    <a href="UserList.php">Users</a>
    <a href="#">Games</a>
    <a href="#">Tournaments</a>
    <a href="#">Feedback</a>
    <a href="#">Rewards</a>
  </div>

  <!-- ===== Main Content ===== -->
  <div class="main-content">
    <h1> GameBridge Admin Dashboard</h1>

    <!-- ===== Stats Overview ===== -->
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

    <!-- ===== User Table ===== -->
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
    <?php
      // نعرض فقط أول 3 مستخدمين
      $previewUsers = array_slice($users, 0, 3);
    ?>

    <?php if (!empty($previewUsers)): ?>
      <?php foreach ($previewUsers as $u): ?>
        <tr>
          <td>@<?= htmlspecialchars($u['username']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['user_role']) ?></td>
          <td>
            <a href="update.php?id=<?= $u['id'] ?>">
              <button>Update</button>
            </a>
            <a href="delete.php?id=<?= $u['id'] ?>"
               onclick="return confirm('Delete this user?');">
              <button style="border-color:red; color:red;">Delete</button>
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
  Showing latest 3 users • Manage all users from the <strong>Users</strong> menu on the left.
</p>


    <!-- ===== Games Table ===== -->
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
          <td>
            <button>Delete</button>
          </td>
        </tr>
        <tr>
          <td>Dead Curse</td>
          <td>@AceGamer</td>
          <td>5,403</td>
          <td>
            <button>Delete</button>
          </td>
        </tr>
        <tr>
          <td>Memory Quest</td>
          <td>@LunaDev</td>
          <td>12,070</td>
          <td>
            <button>Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <footer>© 2025 GameBridge • Admin Panel</footer>
  </div>
</body>
</html>
