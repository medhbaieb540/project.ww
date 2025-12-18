<?php
// list.php - Main entry point for game management
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection and controllers
try {
    $db_file = __DIR__ . '/../../config/db.php';
    if (!file_exists($db_file)) {
        die("Database config file not found: $db_file");
    }
    require_once $db_file;
    
    $game_controller_file = __DIR__ . '/../../Controller/GameController.php';
    if (!file_exists($game_controller_file)) {
        die("GameController file not found: $game_controller_file");
    }
    require_once $game_controller_file;
    
    $category_controller_file = __DIR__ . '/../../Controller/CategoryController.php';
    if (!file_exists($category_controller_file)) {
        die("CategoryController file not found: $category_controller_file");
    }
    require_once $category_controller_file;
    
    $review_controller_file = __DIR__ . '/../../Controller/ReviewController.php';
    if (!file_exists($review_controller_file)) {
        die("ReviewController file not found: $review_controller_file");
    }
    require_once $review_controller_file;
    
    if (!isset($pdo)) {
        die("Database connection failed - PDO not initialized");
    }
} catch (Exception $e) {
    die("Error loading files: " . $e->getMessage());
}

// Get controller and action from URL
$controller = $_GET['controller'] ?? 'game';
$action = $_GET['action'] ?? 'index';

try {
    // Route to appropriate controller
    switch ($controller) {
        case 'game':
            $gameController = new GameController($pdo);
            if (method_exists($gameController, $action)) {
                // For index action, we'll capture output and continue to display
                if ($action === 'index') {
                    // Fetch games directly from database instead of using model
                    try {
                        $games_stmt = $pdo->prepare("SELECT g.*, c.name as category_name, u.username as developer_name FROM games g LEFT JOIN categories c ON g.category_id = c.category_id LEFT JOIN users u ON g.developer_id = u.id ORDER BY g.created_at DESC");
                        $games_stmt->execute();
                        $games = $games_stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        $categories_stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name ASC");
                        $categories_stmt->execute();
                        $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Get current user role
                        $user_role = 'player';
                        if (!empty($_SESSION['user_id'])) {
                            $user_stmt = $pdo->prepare("SELECT user_role FROM users WHERE id = :id");
                            $user_stmt->bindParam(':id', $_SESSION['user_id']);
                            $user_stmt->execute();
                            $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
                            $user_role = $user_data['user_role'] ?? 'player';
                        }
                        
                        $deletedGames = [];
                        $deletedCount = 0;
                    } catch (Exception $e) {
                        error_log("Direct query error: " . $e->getMessage());
                        $games = [];
                        $categories = [];
                        $deletedGames = [];
                        $deletedCount = 0;
                    }
                } else {
                    $gameController->$action();
                    exit;
                }
            } else {
                $_SESSION['error'] = 'Action not found';
            }
            break;
            
        case 'category':
            $categoryController = new CategoryController($pdo);
            if (method_exists($categoryController, $action)) {
                $categoryController->$action();
                exit;
            } else {
                $_SESSION['error'] = 'Action not found';
            }
            break;
            
        case 'review':
            $reviewController = new ReviewController($pdo);
            if (method_exists($reviewController, $action)) {
                $reviewController->$action();
                exit;
            } else {
                $_SESSION['error'] = 'Action not found';
            }
            break;
            
        default:
            $_SESSION['error'] = 'Controller not found';
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
    error_log("EXCEPTION IN LIST.PHP: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $games = [];
    $categories = [];
    $deletedGames = [];
    $deletedCount = 0;
}

// Ensure variables are set
$games = $games ?? [];
$categories = $categories ?? [];
$deletedGames = $deletedGames ?? [];
$deletedCount = $deletedCount ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GameBridge | Games</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Orbitron:wght@400;700;900&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --bg-dark: #0a0a0a;
            --bg-card: #111;
            --text: #e0e0e0;
            --accent: #1aff87;
        }
        
        html, body {
            width: 100%;
            background: var(--bg-dark);
            color: var(--text);
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: #000;
            border-bottom: 1px solid var(--accent);
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-container img {
            width: 45px;
            height: 45px;
            border-radius: 8px;
        }
        
        nav {
            display: flex;
            gap: 1px;
            margin-left: auto;
            align-items: center;
        }
        
        nav a {
            color: var(--text);
            text-decoration: none;
            margin: 0 12px;
            font-weight: 500;
            transition: 0.3s;
        }
        
        nav a:hover, nav a.active {
            color: var(--accent);
        }
        
        .logout-btn {
            background: #ff4d4d;
            color: #000;
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: 700;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            opacity: 0.9;
        }
        
        section {
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #1aff8720;
            border: 1px solid #1aff87;
            color: #1aff87;
        }
        
        .alert-error {
            background: #ff336620;
            border: 1px solid #ff3366;
            color: #ff3366;
        }
        
        .search-filter-section {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 12px;
            background: #111;
            border: 1px solid #1aff8715;
            border-radius: 6px;
            color: var(--text);
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 10px #1aff8722;
        }
        
        .filter-select {
            padding: 12px;
            background: #111;
            border: 1px solid #1aff8715;
            border-radius: 6px;
            color: var(--accent);
            font-size: 0.95rem;
            min-width: 150px;
            font-family: 'Poppins', sans-serif;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: var(--accent);
        }
        
        .apply-btn {
            background: var(--accent);
            color: #000;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .apply-btn:hover {
            background: #11cc66;
            transform: translateY(-2px);
        }
        
        .upload-btn {
            background: var(--accent);
            color: #000;
            padding: 12px 25px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            display: inline-block;
            margin-bottom: 30px;
        }
        
        .upload-btn:hover {
            background: #11cc66;
            transform: translateY(-2px);
        }
        
        .game-card {
            background: var(--bg-card);
            border: 1px solid #1aff8720;
            border-radius: 12px;
            padding: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: 0.3s;
        }
        
        .game-card:hover {
            border-color: var(--accent);
            transform: translateY(-5px);
            box-shadow: 0 0 25px #1aff8733;
        }
        
        .game-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .game-card h3 {
            padding: 15px 15px 5px 15px;
            color: var(--accent);
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            margin: 0;
        }
        
        .game-info {
            padding: 0 15px;
            color: #ccc;
            font-size: 0.9rem;
            margin: 5px 0;
        }
        
        .game-actions {
            padding: 15px;
            display: flex;
            gap: 10px;
            margin-top: auto;
        }
        
        .btn-play, .btn-review {
            flex: 1;
            text-align: center;
            padding: 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: 0.3s;
            text-transform: uppercase;
            border: 2px solid var(--accent);
            color: var(--accent);
            background: transparent;
        }
        
        .btn-play:hover, .btn-review:hover {
            background: var(--accent);
            color: #000;
        }
        
        .advanced-controls {
            background: var(--bg-card);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #1aff8720;
            margin-bottom: 30px;
        }
        
        .controls-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .view-toggle {
            display: flex;
            gap: 10px;
        }
        
        .view-btn {
            padding: 8px 15px;
            background: #111;
            border: 1px solid #1aff8715;
            border-radius: 6px;
            color: var(--text);
            cursor: pointer;
            transition: 0.3s;
        }
        
        .view-btn.active {
            background: var(--accent);
            color: #000;
            border-color: var(--accent);
        }
        
        .games-grid-view {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .no-games {
            text-align: center;
            padding: 40px;
            color: #888;
        }
        
        .stats-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: var(--bg-card);
            border-radius: 12px;
            border: 1px solid #1aff8720;
            margin-bottom: 20px;
        }
        
        .stats-bar span {
            color: var(--accent);
            font-weight: 600;
        }
        
        .bulk-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .bulk-actions select {
            padding: 8px 15px;
            background: #111;
            border: 1px solid #1aff8715;
            border-radius: 6px;
            color: var(--accent);
            cursor: pointer;
        }
        .bulk-actions button {
            padding: 8px 15px;
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .bulk-actions button:hover {
            background: #11cc66;
        }
        .trash-toggle, .favorites-toggle {
            padding: 10px 18px;
            background: #111;
            border: 1px solid #1aff8715;
            border-radius: 8px;
            color: var(--accent);
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .trash-toggle:hover, .favorites-toggle:hover {
            border-color: var(--accent);
            background: #1aff8720;
        }
        .trash-panel, .favorites-panel {
            background: var(--bg-card);
            border: 1px solid #1aff8720;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            display: none;
        }
        .trash-panel.active, .favorites-panel.active {
            display: block;
        }
        .favorite-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 28px;
            height: 28px;
            border: none;
            background: #00000080;
            color: #ccc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.3s;
        }
        .favorite-btn:hover {
            color: var(--accent);
            background: #000000b3;
        }
        .favorite-btn.favorited {
            color: #ff6b9a;
        }
        .favorites-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
        }
        .favorite-item {
            background: #111;
            border: 1px solid #1aff8715;
            border-radius: 10px;
            padding: 12px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .favorite-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }
        .favorite-item-title {
            color: var(--accent);
            font-weight: 600;
            margin: 0;
        }
        .empty-note {
            color: #888;
            padding: 10px 0;
        }
        .game-card input[type="checkbox"] {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            z-index: 10;
        }
        .game-card {
            position: relative;
        }
        .advanced-filters {
            display: none;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #1aff8715;
        }
        .advanced-filters.active {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            color: var(--accent);
            font-size: 0.85rem;
            font-weight: 600;
        }
        .filter-group input {
            padding: 8px;
            background: #111;
            border: 1px solid #1aff8715;
            border-radius: 6px;
            color: var(--text);
        }
        .toggle-advanced {
            padding: 8px 15px;
            background: transparent;
            border: 1px solid var(--accent);
            border-radius: 6px;
            color: var(--accent);
            cursor: pointer;
            transition: 0.3s;
        }
        .toggle-advanced:hover {
            background: var(--accent);
            color: #000;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
            align-items: center;
        }
        .pagination a, .pagination span {
            padding: 10px 15px;
            background: var(--bg-card);
            border: 1px solid #1aff8720;
            border-radius: 6px;
            color: var(--text);
            text-decoration: none;
            transition: 0.3s;
        }
        .pagination a:hover {
            border-color: var(--accent);
            background: #1aff8720;
        }
        .pagination .active {
            background: var(--accent);
            color: #000;
            border-color: var(--accent);
        }
        .games-list-view {
            display: none;
        }
        .games-list-view.active {
            display: block;
        }
        .games-grid-view.active {
            display: grid;
        }
        .list-item {
            background: var(--bg-card);
            border: 1px solid #1aff8720;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            gap: 20px;
            align-items: center;
            transition: 0.3s;
            position: relative;
        }
        .list-item:hover {
            border-color: var(--accent);
            transform: translateX(5px);
        }
        .list-item img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }
        .list-item-content {
            flex: 1;
        }
        .list-item-actions {
            display: flex;
            gap: 10px;
        }
        .list-item input[type="checkbox"] {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            z-index: 10;
        }
        .stats-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: var(--bg-card);
            border-radius: 12px;
            border: 1px solid #1aff8720;
            margin-bottom: 20px;
        }
        .stats-bar span {
            color: var(--accent);
            font-weight: 600;
        }
        .favorite-toggle {
            padding: 10px 18px;
            background: #5b4bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
        }
        .favorite-toggle:hover {
            background: #4a3aee;
        }
        .favorite-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.6);
            color: #fff;
            border: 2px solid #fff;
            border-radius: 50%;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.2s;
            z-index: 11;
            font-size: 16px;
        }
        .favorite-btn:hover {
            transform: scale(1.05);
            border-color: var(--accent);
        }
        .favorite-btn.active {
            background: #ff3366;
            border-color: #ff3366;
        }
        .favorites-panel {
            background: var(--bg-card);
            border: 1px solid #1aff8720;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: none;
        }
        .favorites-panel.active {
            display: block;
        }
        .favorites-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }
        .favorite-chip {
            background: #111;
            border: 1px solid #1aff8715;
            border-radius: 10px;
            padding: 12px;
            color: #ccc;
            position: relative;
        }
        .favorite-chip strong {
            color: var(--accent);
        }
        .favorite-chip button {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #ff3366;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 12px;
            cursor: pointer;
        }
        .logo-container {
  display: flex;
  align-items: center;
  gap: 10px;
}

.logo-container img {
  width: 40px;
  height: 40px;
  border-radius: 8px;
}
     .logout-btn{
  background:#ff4d4d;
  color:#000;
  padding:8px 14px;
  border-radius:10px;
  font-weight:700;
}
.logout-btn:hover{
  opacity:.9;
}
    </style>
</head>
<body>
    <header>
         <div class="logo-container">
      <img src="../../public/images/logo.png" alt="Logo" class="logo">
    </div>
       
        <nav>
      <a href="../index.html">Home</a>
      <a href="list.php"class="active">Games</a>
      <a href="tournaments.php">Tournaments</a>
      <a href="community.php">Community</a>
      <a href="event.php">Events</a>
      <a href="feedback.php">Feedback</a>
      <a href="profile.php" >My Profile</a>
      <a href="logout.php"
         onclick="return confirm('Are you sure you want to logout?');"
         class="logout-btn">Logout</a>
    </nav>
    </header>

    <section>
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

        <div class="advanced-controls">
            <div class="controls-header">
                <div>
                    <?php if (isset($user_role) && $user_role === 'developer'): ?>
                    <a href="list.php?controller=game&action=create" class="upload-btn">+ Upload New Game</a>
                    <?php endif; ?>
                </div>
                <div class="view-toggle">
                    <button class="view-btn active" onclick="setView('grid')" id="gridViewBtn">⊞ Grid</button>
                    <button class="view-btn" onclick="setView('list')" id="listViewBtn">☰ List</button>
                </div>
            </div>
            
            <div class="search-filter-section">
                <input type="text" id="searchInput" class="search-input" placeholder="🔍 Search games by title or description..." />
                <select id="category-filter" class="filter-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select id="sort-select" class="filter-select">
                    <option value="created_at|DESC">Newest First</option>
                    <option value="created_at|ASC">Oldest First</option>
                    <option value="title|ASC">Title A-Z</option>
                    <option value="title|DESC">Title Z-A</option>
                    <option value="average_rating|DESC">Highest Rated</option>
                    <option value="average_rating|ASC">Lowest Rated</option>
                </select>
                <button onclick="applyFilters()" class="apply-btn">Apply</button>
                <button onclick="toggleAdvancedFilters()" class="toggle-advanced" id="toggleAdvancedBtn">⚙️ Advanced</button>
            </div>
            
            <div class="advanced-filters" id="advancedFilters">
                <div class="filter-group">
                    <label>Min Rating</label>
                    <input type="number" id="minRating" min="0" max="5" step="0.1" placeholder="0.0">
                </div>
                <div class="filter-group">
                    <label>Max Rating</label>
                    <input type="number" id="maxRating" min="0" max="5" step="0.1" placeholder="5.0">
                </div>
                <div class="filter-group">
                    <label>From Date</label>
                    <input type="date" id="fromDate">
                </div>
                <div class="filter-group">
                    <label>To Date</label>
                    <input type="date" id="toDate">
                </div>
                <div class="filter-group" style="align-self: flex-end;">
                    <button onclick="applyAdvancedFilters()" class="apply-btn">Apply Filters</button>
                </div>
            </div>
            
            <div class="bulk-actions" id="bulkActions" style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px solid #1aff8715;">
                <span style="color: var(--accent); font-weight: 600;" id="selectedCount">0 selected</span>
                <select id="bulkAction">
                    <option value="">Bulk Actions</option>
                    <option value="delete">Delete Selected</option>
                </select>
                <button onclick="executeBulkAction()">Apply</button>
            </div>
        </div>
        
        <div class="stats-bar">
            <span>📊 Total Games: <strong><?php echo count($games); ?></strong></span>
            <span id="filteredCount">Showing: <strong><?php echo count($games); ?></strong> games</span>
            <div style="display: flex; gap: 10px; align-items: center;">
            <button class="trash-toggle" onclick="toggleTrash()">🗑️ Trash</button>
                <button class="favorites-toggle" onclick="toggleFavoritesPanel()" id="favoriteToggleBtn">♡ Favorites (<span id="favoriteCountBadge">0</span>)</button>
            </div>
        </div>

        <div class="trash-panel" id="trashPanel">
            <?php if (empty($deletedGames)): ?>
                <div class="empty-note">No deleted games yet.</div>
            <?php else: ?>
                <div class="table-container" style="margin:0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Developer</th>
                                <th>Deleted At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deletedGames as $game): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($game['title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($game['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td><?php echo htmlspecialchars($game['developer_name'] ?? 'Unknown'); ?></td>
                                    <td><?php echo date('F j, Y, g:i a', strtotime($game['deleted_at'])); ?></td>
                                    <td>
                                        <a href="list.php?controller=game&action=restore&id=<?php echo $game['game_id']; ?>" class="btn-review" style="border-color: var(--accent); color: var(--accent);">Restore</a>
                                        <a href="list.php?controller=game&action=permanentDelete&id=<?php echo $game['game_id']; ?>" class="btn-review" style="border-color: #ff3366; color: #ff3366;" onclick="return confirm('Permanently delete this game? This cannot be undone.')">Delete Forever</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="favorites-panel" id="favoritesPanel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <strong style="color: var(--accent);">❤️ Favorites</strong>
                <button class="toggle-advanced" onclick="clearFavorites()">Clear Favorites</button>
            </div>
            <div id="favoritesEmpty" class="empty-note">No favorites yet. Tap the heart on a game to add it.</div>
            <div class="favorites-list" id="favoritesList" style="display:none;"></div>
        </div>

        <!-- Grid View -->
        <div class="games-grid games-grid-view active" id="gridView">
            <!-- DEBUG: Games count = <?php echo count($games); ?> -->
            <?php if (empty($games)): ?>
                
            <?php else: ?>
                <?php foreach ($games as $game): ?>
                    <div class="game-card"
                         data-game-id="<?php echo $game['game_id']; ?>"
                         data-created-date="<?php echo $game['created_at']; ?>"
                         data-title="<?php echo htmlspecialchars($game['title']); ?>"
                         data-category="<?php echo htmlspecialchars($game['category_name'] ?? 'Uncategorized'); ?>"
                         data-developer="<?php echo htmlspecialchars($game['developer_name'] ?? 'Unknown Developer'); ?>"
                         data-rating="<?php echo number_format($game['average_rating'] ?? 0, 1); ?>"
                         data-image="<?php echo !empty($game['image_path']) ? '../../' . htmlspecialchars($game['image_path']) : '../../assets/images/game1.jpg'; ?>">
                        <input type="checkbox" class="game-checkbox" value="<?php echo $game['game_id']; ?>" onchange="updateBulkActions()">
                        <button class="favorite-btn" onclick="toggleFavorite(<?php echo $game['game_id']; ?>, this); return false;" aria-label="Toggle favorite">♡</button>
                        <?php if (!empty($game['image_path'])): ?>
                            <img src="../../<?php echo htmlspecialchars($game['image_path']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" />
                        <?php else: ?>
                            <img src="../../assets/images/game1.jpg" alt="No Image" />
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($game['title']); ?></h3>
                        <p class="game-info">
                            By <b><?php echo htmlspecialchars($game['developer_name'] ?? 'Unknown Developer'); ?></b> • 
                            <?php echo htmlspecialchars($game['category_name'] ?? 'Uncategorized'); ?> • 
                            ★ <?php echo number_format($game['average_rating'] ?? 0, 1); ?>
                        </p>
                        <div class="game-actions">
                                <?php if (!empty($game['file_path'])): ?>
                                    <a href="../../<?php echo htmlspecialchars($game['file_path']); ?>" class="btn-play" download>PLAY</a>
                                <?php else: ?>
                                    <a href="#" class="btn-play" onclick="alert('Game file not available'); return false;">PLAY</a>
                                <?php endif; ?>
                                <a href="review.php?action=create&game_id=<?php echo $game['game_id']; ?>" class="btn-review">REVIEW</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- List View -->
        <div class="games-list-view" id="listView">
            <?php if (empty($games)): ?>
                <div class="no-games">
                    <p>No games found. <a href="../../public/index.php?controller=game&action=create">Create your first game!</a></p>
                </div>
            <?php else: ?>
                <?php foreach ($games as $game): ?>
                    <div class="list-item"
                         data-game-id="<?php echo $game['game_id']; ?>"
                         data-created-date="<?php echo $game['created_at']; ?>"
                         data-title="<?php echo htmlspecialchars($game['title']); ?>"
                         data-category="<?php echo htmlspecialchars($game['category_name'] ?? 'Uncategorized'); ?>"
                         data-developer="<?php echo htmlspecialchars($game['developer_name'] ?? 'Unknown Developer'); ?>"
                         data-rating="<?php echo number_format($game['average_rating'] ?? 0, 1); ?>"
                         data-image="<?php echo !empty($game['image_path']) ? '../../' . htmlspecialchars($game['image_path']) : '../../assets/images/game1.jpg'; ?>">
                        <input type="checkbox" class="game-checkbox" value="<?php echo $game['game_id']; ?>" onchange="updateBulkActions()">
                        <button class="favorite-btn" onclick="toggleFavorite(<?php echo $game['game_id']; ?>, this); return false;" aria-label="Toggle favorite">♡</button>
                        <?php if (!empty($game['image_path'])): ?>
                            <img src="../../<?php echo htmlspecialchars($game['image_path']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" />
                        <?php else: ?>
                            <img src="../../assets/images/game1.jpg" alt="No Image" />
                        <?php endif; ?>
                        <div class="list-item-content">
                            <h3 style="color: var(--accent); font-family: 'Orbitron', sans-serif; margin: 0 0 10px 0;">
                                <?php echo htmlspecialchars($game['title']); ?>
                            </h3>
                            <p style="color: #ccc; margin: 5px 0;">
                                By <b><?php echo htmlspecialchars($game['developer_name'] ?? 'Unknown Developer'); ?></b> • 
                                <?php echo htmlspecialchars($game['category_name'] ?? 'Uncategorized'); ?> • 
                                ★ <?php echo number_format($game['average_rating'] ?? 0, 1); ?> • 
                                Created: <?php echo date('M j, Y', strtotime($game['created_at'])); ?>
                            </p>
                            <p style="color: #888; font-size: 0.9rem; margin: 5px 0;">
                                <?php echo htmlspecialchars(substr($game['description'] ?? '', 0, 150)); ?>...
                            </p>
                        </div>
                        <div class="list-item-actions">
                            <?php if (!empty($game['file_path'])): ?>
                                <a href="../../<?php echo htmlspecialchars($game['file_path']); ?>" class="btn-play" download>PLAY</a>
                            <?php else: ?>
                                <a href="#" class="btn-play" onclick="alert('Game file not available'); return false;">PLAY</a>
                            <?php endif; ?>
                            <a href="../../Controller/ReviewController.php?action=create&game_id=<?php echo $game['game_id']; ?>" class="btn-review">REVIEW</a>
                            <a href="../../public/index.php?controller=game&action=show&id=<?php echo $game['game_id']; ?>" class="btn-review" style="background: #5b4bff; border-color: #5b4bff; color: #fff;">VIEW</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if (count($games) > 12): ?>
        <div class="pagination">
            <a href="#" onclick="changePage(1); return false;">« First</a>
            <a href="#" onclick="changePage(currentPage - 1); return false;">‹ Prev</a>
            <span class="active">Page 1</span>
            <a href="#" onclick="changePage(2); return false;">2</a>
            <a href="#" onclick="changePage(3); return false;">3</a>
            <a href="#" onclick="changePage(currentPage + 1); return false;">Next ›</a>
            <a href="#" onclick="changePage(totalPages); return false;">Last »</a>
        </div>
        <?php endif; ?>
    </section>

    <script>
        let currentView = 'grid';
        let currentPage = 1;
        let totalPages = Math.ceil(<?php echo count($games); ?> / 12);
        
        function setView(view) {
            currentView = view;
            const gridView = document.getElementById('gridView');
            const listView = document.getElementById('listView');
            const gridBtn = document.getElementById('gridViewBtn');
            const listBtn = document.getElementById('listViewBtn');
            
            if (view === 'grid') {
                gridView.classList.add('active');
                listView.classList.remove('active');
                gridBtn.classList.add('active');
                listBtn.classList.remove('active');
            } else {
                gridView.classList.remove('active');
                listView.classList.add('active');
                gridBtn.classList.remove('active');
                listBtn.classList.add('active');
            }
        }
        
        function toggleAdvancedFilters() {
            const filters = document.getElementById('advancedFilters');
            const btn = document.getElementById('toggleAdvancedBtn');
            filters.classList.toggle('active');
            if (filters.classList.contains('active')) {
                btn.textContent = '⚙️ Hide Advanced';
            } else {
                btn.textContent = '⚙️ Advanced';
            }
        }
        
        function applyFilters() {
            const baseUrl = 'list.php?controller=game&action=';
            const searchTerm = document.getElementById('searchInput').value.trim();
            const categoryId = document.getElementById('category-filter').value;
            const sortValue = document.getElementById('sort-select').value;
            const [sortBy, order] = sortValue.split('|');
            
            let url;
            
            // Priority: Search > Filter > Sort
            if (searchTerm) {
                url = 'list.php?controller=game&action=search&q=' + encodeURIComponent(searchTerm);
            } else if (categoryId) {
                url = 'list.php?controller=game&action=filter&category_id=' + categoryId;
            } else {
                url = 'list.php?controller=game&action=sort&sort_by=' + sortBy + '&order=' + order;
            }
            
            window.location.href = url;
        }
        
        function applyAdvancedFilters() {
            const minRating = document.getElementById('minRating').value;
            const maxRating = document.getElementById('maxRating').value;
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;
            
            // Filter games client-side for now (can be enhanced with server-side filtering)
            const games = document.querySelectorAll('.game-card, .list-item');
            let visibleCount = 0;
            
            games.forEach(game => {
                const rating = parseFloat(game.querySelector('.game-info')?.textContent.match(/★ ([\d.]+)/)?.[1] || 0);
                const gameDate = new Date(game.dataset.createdDate || '2000-01-01');
                const from = fromDate ? new Date(fromDate) : null;
                const to = toDate ? new Date(toDate) : null;
                
                let show = true;
                
                if (minRating && rating < parseFloat(minRating)) show = false;
                if (maxRating && rating > parseFloat(maxRating)) show = false;
                if (from && gameDate < from) show = false;
                if (to && gameDate > to) show = false;
                
                if (show) {
                    game.style.display = '';
                    visibleCount++;
                } else {
                    game.style.display = 'none';
                }
            });
            
            document.getElementById('filteredCount').innerHTML = 'Showing: <strong>' + visibleCount + '</strong> games';
        }
        
        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.game-checkbox:checked');
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');
            
            if (checkboxes.length > 0) {
                bulkActions.style.display = 'flex';
                selectedCount.textContent = checkboxes.length + ' selected';
            } else {
                bulkActions.style.display = 'none';
            }
        }
        
        function executeBulkAction() {
            const action = document.getElementById('bulkAction').value;
            const selected = Array.from(document.querySelectorAll('.game-checkbox:checked')).map(cb => cb.value);
            
            if (!action || selected.length === 0) {
                alert('Please select an action and at least one game');
                return;
            }
            
            if (action === 'delete') {
                if (confirm('Are you sure you want to delete ' + selected.length + ' game(s)?')) {
                    selected.forEach(id => {
                        window.location.href = 'list.php?controller=game&action=delete&id=' + id;
                    });
                }
            }
        }

        // ===== Favorites handling (client-side with localStorage) =====
        const FAVORITES_KEY = 'favoriteGames';

        function getFavorites() {
            try {
                return JSON.parse(localStorage.getItem(FAVORITES_KEY)) || [];
            } catch (e) {
                return [];
            }
        }

        function saveFavorites(ids) {
            localStorage.setItem(FAVORITES_KEY, JSON.stringify(ids));
        }

        function toggleFavorite(id, btn) {
            const favorites = getFavorites();
            const idStr = String(id);
            const existingIndex = favorites.indexOf(idStr);

            if (existingIndex >= 0) {
                favorites.splice(existingIndex, 1);
            } else {
                favorites.push(idStr);
            }

            saveFavorites(favorites);
            updateFavoriteButtons();
            renderFavorites();
        }

        function updateFavoriteButtons() {
            const favorites = getFavorites();
            document.querySelectorAll('.favorite-btn').forEach(btn => {
                const card = btn.closest('[data-game-id]');
                const id = card?.dataset.gameId;
                if (id && favorites.includes(String(id))) {
                    btn.classList.add('favorited');
                    btn.textContent = '♥';
                } else {
                    btn.classList.remove('favorited');
                    btn.textContent = '♡';
                }
            });
            const toggleBtn = document.getElementById('favoriteToggleBtn');
            if (toggleBtn) {
                toggleBtn.innerHTML = `♡ Favorites (<span id="favoriteCountBadge">${favorites.length}</span>)`;
            }
            const badge = document.getElementById('favoriteCountBadge');
            if (badge) badge.textContent = favorites.length;
        }

        function renderFavorites() {
            const favorites = getFavorites();
            const panel = document.getElementById('favoritesPanel');
            const list = document.getElementById('favoritesList');
            const empty = document.getElementById('favoritesEmpty');
            list.innerHTML = '';

            if (favorites.length === 0) {
                empty.style.display = 'block';
                list.style.display = 'none';
                return;
            }

            empty.style.display = 'none';
            list.style.display = 'grid';

            favorites.forEach(id => {
                const card = document.querySelector(`[data-game-id="${id}"]`);
                if (!card) return;
                const data = card.dataset;
                const item = document.createElement('div');
                item.className = 'favorite-chip';
                item.innerHTML = `
                    <strong>${data.title}</strong><br/>
                    <span>${data.category} • ${data.developer} • ★ ${data.rating}</span>
                    <button onclick="toggleFavorite('${id}')">Remove</button>
                `;
                list.appendChild(item);
            });
        }

        function toggleFavoritesPanel() {
            const panel = document.getElementById('favoritesPanel');
            panel.classList.toggle('active');
            if (panel.classList.contains('active')) {
                renderFavorites();
            }
        }

        function toggleTrash() {
            const panel = document.getElementById('trashPanel');
            panel.classList.toggle('active');
        }

        function clearFavorites() {
            saveFavorites([]);
            updateFavoriteButtons();
            renderFavorites();
        }
        
        function changePage(page) {
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            // Implement pagination logic here
            // For now, just scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyFilters();
            }
        });
        
        // Real-time search
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const term = this.value.toLowerCase();
                const games = document.querySelectorAll('.game-card, .list-item');
                let visibleCount = 0;
                
                games.forEach(game => {
                    const text = game.textContent.toLowerCase();
                    if (text.includes(term)) {
                        game.style.display = '';
                        visibleCount++;
                    } else {
                        game.style.display = 'none';
                    }
                });
                
                document.getElementById('filteredCount').innerHTML = 'Showing: <strong>' + visibleCount + '</strong> games';
            }, 300);
        });

        // Init favorites on page load
        document.addEventListener('DOMContentLoaded', () => {
            updateFavoriteButtons();
            renderFavorites();
        });
    </script>
</body>
</html>
