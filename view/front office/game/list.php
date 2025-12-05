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
    <title>GameBridge | Games</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/style.css" />
    <style>
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
        .game-card .game-info {
            padding: 0 15px;
            color: #ccc;
            font-size: 0.9rem;
            margin: 5px 0;
        }
        .game-card .game-actions {
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
    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="<?= $baseUrl ?>/assets/images/logo.png" alt="GameBridge Logo">
        </div>
        <nav>
            <a href="<?= $baseUrl ?>/assets/index.html">Home</a>
            <a href="<?= $baseUrl ?>/index.php?controller=game&action=index" class="active">Games</a>
            <a href="#">Tournaments</a>
            <a href="#">Community</a>
            <a href="#">My Profile</a>
            <a href="#">Feedback</a>
            <a href="#">Rewards</a>
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

        <a href="<?= $baseUrl ?>/index.php?controller=game&action=create" class="upload-btn">Upload New Game</a>

        <div class="search-filter-section">
            <input type="text" id="searchInput" class="search-input" placeholder="Search games..." />
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
                <option value="title|ASC">Title A-Z</option>
                <option value="title|DESC">Title Z-A</option>
                <option value="average_rating|DESC">Highest Rated</option>
            </select>
            <button onclick="applyFilters()" class="apply-btn">Apply</button>
        </div>

        <div class="games-grid">
            <?php if (empty($games)): ?>
                <div class="no-games">
                    <p>No games found. <a href="<?= $baseUrl ?>/index.php?controller=game&action=create">Create your first game!</a></p>
                </div>
            <?php else: ?>
                <?php foreach ($games as $game): ?>
                    <div class="game-card">
                        <?php if (!empty($game['image_path'])): ?>
                            <img src="<?= $baseUrl ?>/<?php echo htmlspecialchars($game['image_path']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" />
                        <?php else: ?>
                            <img src="<?= $baseUrl ?>/assets/images/game1.jpg" alt="No Image" />
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($game['title']); ?></h3>
                        <p class="game-info">
                            By <b><?php echo htmlspecialchars($game['developer_name'] ?? 'Unknown Developer'); ?></b> • 
                            <?php echo htmlspecialchars($game['category_name'] ?? 'Uncategorized'); ?> • 
                            ★ <?php echo number_format($game['average_rating'] ?? 0, 1); ?>
                        </p>
                        <div class="game-actions">
                                <?php if (!empty($game['file_path'])): ?>
                                    <a href="<?= $baseUrl ?>/<?php echo htmlspecialchars($game['file_path']); ?>" class="btn-play" download>PLAY</a>
                                <?php else: ?>
                                    <a href="#" class="btn-play" onclick="alert('Game file not available'); return false;">PLAY</a>
                                <?php endif; ?>
                                <a href="<?= $baseUrl ?>/index.php?controller=review&action=create&game_id=<?php echo $game['game_id']; ?>" class="btn-review">REVIEW</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <script>
        function applyFilters() {
            const baseUrl = '<?= $baseUrl ?>/index.php?controller=game&action=';
            const searchTerm = document.getElementById('searchInput').value.trim();
            const categoryId = document.getElementById('category-filter').value;
            const sortValue = document.getElementById('sort-select').value;
            const [sortBy, order] = sortValue.split('|');
            
            let url;
            
            // Priority: Search > Filter > Sort
            if (searchTerm) {
                url = '<?= $baseUrl ?>/index.php?controller=game&action=search&q=' + encodeURIComponent(searchTerm);
            } else if (categoryId) {
                url = '<?= $baseUrl ?>/index.php?controller=game&action=filter&category_id=' + categoryId;
            } else {
                url = '<?= $baseUrl ?>/index.php?controller=game&action=sort&sort_by=' + sortBy + '&order=' + order;
            }
            
            window.location.href = url;
        }
        
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyFilters();
            }
        });
    </script>
</body>
</html>
