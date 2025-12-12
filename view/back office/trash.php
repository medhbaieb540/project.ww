<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$baseUrl = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GameBridge | Trash</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/style.css" />
    <style>
        body {
            background: var(--bg-dark);
            color: var(--text);
            font-family: 'Poppins', sans-serif;
        }
        .trash-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .trash-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--accent);
        }
        .trash-header h1 {
            font-family: 'Orbitron', sans-serif;
            color: var(--accent);
            font-size: 2.5rem;
        }
        .nav-links {
            display: flex;
            gap: 15px;
        }
        .nav-links a {
            padding: 10px 20px;
            background: var(--bg-card);
            color: var(--text);
            text-decoration: none;
            border-radius: 6px;
            border: 1px solid #1aff8720;
            transition: 0.3s;
        }
        .nav-links a:hover {
            border-color: var(--accent);
            background: #1aff8720;
        }
        .trash-info {
            background: var(--bg-card);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #1aff8720;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .trash-info p {
            color: #888;
            margin: 0;
        }
        .trash-info .count {
            color: var(--accent);
            font-weight: 600;
            font-size: 1.2rem;
        }
        .btn-empty-trash {
            padding: 12px 25px;
            background: #ff3366;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: 0.3s;
            border: 2px solid #ff3366;
        }
        .btn-empty-trash:hover {
            background: #ff1144;
            transform: translateY(-2px);
        }
        .table-container {
            background: var(--bg-card);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #1aff8720;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            color: var(--accent);
            font-family: 'Orbitron', sans-serif;
            font-weight: 600;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid var(--accent);
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #333;
            color: var(--text);
        }
        tr:hover {
            background: #0c0c0c;
        }
        .deleted-date {
            color: #888;
            font-size: 0.85rem;
        }
        .btn-restore, .btn-delete-permanent {
            padding: 6px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            border: 2px solid;
            display: inline-block;
            margin-right: 8px;
            transition: 0.3s;
        }
        .btn-restore {
            color: var(--accent);
            border-color: var(--accent);
            background: transparent;
        }
        .btn-restore:hover {
            background: var(--accent);
            color: #000;
        }
        .btn-delete-permanent {
            color: #ff3366;
            border-color: #ff3366;
            background: transparent;
        }
        .btn-delete-permanent:hover {
            background: #ff3366;
            color: #fff;
        }
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            color: #888;
        }
        .empty-state h2 {
            color: var(--accent);
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 15px;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 2px solid;
        }
        .alert-success {
            background: #1aff8720;
            border-color: var(--accent);
            color: var(--accent);
        }
        .alert-error {
            background: #ff336620;
            border-color: #ff3366;
            color: #ff3366;
        }
    </style>
</head>
<body>
    <div class="trash-container">
        <div class="trash-header">
            <h1>🗑️ Trash</h1>
            <div class="nav-links">
                <a href="<?= $baseUrl ?>/index.php?controller=game&action=dashboard">Dashboard</a>
                <a href="<?= $baseUrl ?>/index.php?controller=game&action=index">View Games</a>
                <a href="<?= $baseUrl ?>/index.php?controller=game&action=create">Add Game</a>
                <a href="<?= $baseUrl ?>/assets/index.html">Home</a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="trash-info">
            <div>
                <p>Total deleted games: <span class="count"><?php echo $deletedCount; ?></span></p>
                <p style="font-size: 0.85rem; margin-top: 5px;">Games in trash can be restored or permanently deleted</p>
            </div>
            <?php if ($deletedCount > 0): ?>
                <a href="<?= $baseUrl ?>/index.php?controller=game&action=emptyTrash" 
                   class="btn-empty-trash"
                   onclick="return confirm('⚠️ WARNING: This will permanently delete ALL games in trash. This action cannot be undone!\n\nAre you absolutely sure?')">
                   🗑️ Empty Trash
                </a>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <?php if (empty($deletedGames)): ?>
                <div class="empty-state">
                    <h2>🗑️ Trash is Empty</h2>
                    <p>No deleted games found. Deleted games will appear here.</p>
                    <a href="<?= $baseUrl ?>/index.php?controller=game&action=dashboard" 
                       style="display: inline-block; margin-top: 20px; padding: 12px 25px; background: var(--accent); color: #000; text-decoration: none; border-radius: 6px; font-weight: 600;">
                       Go to Dashboard
                    </a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Developer</th>
                            <th>Deleted Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deletedGames as $game): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($game['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($game['category_name'] ?? 'Uncategorized'); ?></td>
                                <td><?php echo htmlspecialchars($game['developer_name'] ?? 'Unknown'); ?></td>
                                <td class="deleted-date">
                                    <?php echo date('F j, Y, g:i a', strtotime($game['deleted_at'])); ?>
                                </td>
                                <td>
                                    <a href="<?= $baseUrl ?>/index.php?controller=game&action=restore&id=<?php echo $game['game_id']; ?>" 
                                       class="btn-restore"
                                       onclick="return confirm('Restore this game?')">
                                       ↺ Restore
                                    </a>
                                    <a href="<?= $baseUrl ?>/index.php?controller=game&action=permanentDelete&id=<?php echo $game['game_id']; ?>" 
                                       class="btn-delete-permanent"
                                       onclick="return confirm('⚠️ WARNING: This will permanently delete this game and all its files. This action cannot be undone!\n\nAre you sure?')">
                                       🗑️ Delete Forever
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

