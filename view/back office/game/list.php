<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../model/GameModel.php';
require_once __DIR__ . '/../../model/CategoryModel.php';

$gameModel = new GameModel();
$categoryModel = new CategoryModel();
$games = $gameModel->getAllGames();
$categories = $categoryModel->getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GameBridge | Back Office</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/style.css" />
    <style>
        body {
            background: var(--bg-dark);
            color: var(--text);
            padding: 40px;
            font-family: 'Poppins', sans-serif;
        }
        .back-office-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .section-header {
            color: var(--accent);
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            margin: 40px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent);
        }
        .add-category-section {
            background: var(--bg-card);
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #1aff8720;
        }
        .add-category-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }
        .form-group {
            flex: 1;
        }
        .form-group label {
            display: block;
            color: var(--accent);
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            background: #0c0c0c;
            border: 2px solid var(--accent);
            border-radius: 6px;
            color: var(--text);
            font-size: 14px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #11cc66;
        }
        .btn-clear, .btn-add {
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            border: 2px solid var(--accent);
            transition: 0.3s;
        }
        .btn-clear {
            background: transparent;
            color: var(--text);
        }
        .btn-clear:hover {
            background: var(--accent);
            color: #000;
        }
        .btn-add {
            background: var(--accent);
            color: #000;
        }
        .btn-add:hover {
            background: #11cc66;
        }
        .table-container {
            background: var(--bg-card);
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
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
        .btn-edit, .btn-delete {
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
        .btn-edit {
            color: var(--text);
            border-color: var(--accent);
            background: transparent;
        }
        .btn-edit:hover {
            background: var(--accent);
            color: #000;
        }
        .btn-delete {
            color: var(--text);
            border-color: #ff3366;
            background: transparent;
        }
        .btn-delete:hover {
            background: #ff3366;
            color: #fff;
        }
        .games-count {
            color: var(--accent);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="back-office-container">
        <div style="margin-bottom: 30px; display: flex; gap: 15px; align-items: center;">
            <a href="<?php echo BASE_URL; ?>/index.php?controller=game&action=dashboard" 
               style="padding: 12px 25px; background: var(--accent); color: #000; text-decoration: none; border-radius: 6px; font-weight: 600;">
               🎮 Go to Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=game&action=index" 
               style="padding: 12px 25px; background: var(--bg-card); color: var(--text); text-decoration: none; border-radius: 6px; font-weight: 600; border: 1px solid #1aff8720;">
               View Games
            </a>
        </div>
        <!-- Add New Category Section -->
        <h2 class="section-header">| Add New Category</h2>
        <div class="add-category-section">
            <form action="<?php echo BASE_URL; ?>/index.php?controller=category&action=store" method="POST" class="add-category-form">
                <div class="form-group">
                    <label for="category_name">Category Name</label>
                    <input type="text" id="category_name" name="name" placeholder="Enter category name" required />
                </div>
                <button type="button" class="btn-clear" onclick="document.getElementById('category_name').value=''">CLEAR</button>
                <button type="submit" class="btn-add">ADD CATEGORY</button>
            </form>
        </div>

        <!-- Manage Games Section -->
        <h2 class="section-header">| Manage Games</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>TITLE</th>
                        <th>DEVELOPER</th>
                        <th>CATEGORY</th>
                        <th>RELEASE DATE</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($games)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #888;">
                                No games found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($games as $game): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($game['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($game['developer_name'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($game['category_name'] ?? 'Uncategorized'); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($game['created_at'])); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/index.php?controller=game&action=edit&id=<?php echo $game['game_id']; ?>" class="btn-edit">EDIT</a>
                                    <a href="<?php echo BASE_URL; ?>/index.php?controller=game&action=delete&id=<?php echo $game['game_id']; ?>" 
                                       class="btn-delete"
                                       onclick="return confirm('Are you sure you want to delete this game?')">DELETE</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Manage Categories Section -->
        <h2 class="section-header">| Manage Categories</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>CATEGORY NAME</th>
                        <th>GAMES COUNT</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 40px; color: #888;">
                                No categories found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $category): 
                            $gamesCount = count($gameModel->getGamesByCategory($category['category_id']));
                        ?>
                            <tr>
                                <td><?php echo $category['category_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                                <td><span class="games-count"><?php echo $gamesCount; ?></span></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/index.php?controller=category&action=edit&id=<?php echo $category['category_id']; ?>" class="btn-edit">EDIT</a>
                                    <a href="<?php echo BASE_URL; ?>/index.php?controller=category&action=delete&id=<?php echo $category['category_id']; ?>" 
                                       class="btn-delete"
                                       onclick="return confirm('Are you sure you want to delete this category?')">DELETE</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
