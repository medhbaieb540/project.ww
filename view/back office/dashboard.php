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
    <title>GameBridge | Dashboard</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/style.css" />
    <style>
        body {
            background: var(--bg-dark);
            color: var(--text);
            font-family: 'Poppins', sans-serif;
        }
        .dashboard-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--accent);
        }
        .dashboard-header h1 {
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: var(--bg-card);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #1aff8720;
            text-align: center;
            transition: 0.3s;
        }
        .stat-card:hover {
            border-color: var(--accent);
            transform: translateY(-5px);
            box-shadow: 0 0 25px #1aff8733;
        }
        .stat-card h3 {
            color: var(--accent);
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .stat-card p {
            color: #888;
            font-size: 1rem;
        }
        .section {
            background: var(--bg-card);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #1aff8720;
            margin-bottom: 30px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--accent);
        }
        .section-header h2 {
            font-family: 'Orbitron', sans-serif;
            color: var(--accent);
            font-size: 1.5rem;
        }
        .btn-primary {
            padding: 10px 20px;
            background: var(--accent);
            color: #000;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-primary:hover {
            background: #11cc66;
            transform: translateY(-2px);
        }
        .table-container {
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
        .btn-edit, .btn-delete, .btn-view {
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
        .btn-view {
            color: var(--text);
            border-color: #5b4bff;
            background: transparent;
        }
        .btn-view:hover {
            background: #5b4bff;
            color: #fff;
        }
        .review-item {
            background: #0c0c0c;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #333;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .review-header strong {
            color: var(--accent);
        }
        .review-rating {
            color: #ffaa00;
            font-weight: 600;
        }
        .review-comment {
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 10px;
        }
        .review-meta {
            color: #888;
            font-size: 12px;
        }
        .search-filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 12px;
            background: #0c0c0c;
            border: 1px solid #1aff8715;
            border-radius: 6px;
            color: var(--text);
            font-size: 0.95rem;
        }
        .search-input:focus {
            outline: none;
            border-color: var(--accent);
        }
        .filter-select {
            padding: 12px;
            background: #0c0c0c;
            border: 1px solid #1aff8715;
            border-radius: 6px;
            color: var(--accent);
            font-size: 0.95rem;
            min-width: 150px;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>🎮 GameBridge Dashboard</h1>
            <div class="nav-links">
                <a href="<?= $baseUrl ?>/index.php?controller=game&action=index">View Games</a>
                <a href="<?= $baseUrl ?>/index.php?controller=game&action=create">Add Game</a>
                <a href="<?= $baseUrl ?>/assets/index.html">Home</a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo count($games); ?></h3>
                <p>Total Games</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($categories); ?></h3>
                <p>Categories</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($reviews); ?></h3>
                <p>Total Reviews</p>
            </div>
            <div class="stat-card">
                <h3>★ <?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></h3>
                <p>Average Rating</p>
            </div>
        </div>

        <!-- Games Management Section -->
        <div class="section">
            <div class="section-header">
                <h2>🎯 Games Management</h2>
                <a href="<?= $baseUrl ?>/index.php?controller=game&action=create" class="btn-primary">+ Add New Game</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Developer</th>
                            <th>Rating</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($games)): ?>
                            <tr>
                                <td colspan="5" class="empty-state">No games found. <a href="<?= $baseUrl ?>/index.php?controller=game&action=create">Create your first game!</a></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($games as $game): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($game['title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($game['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td><?php echo htmlspecialchars($game['developer_name'] ?? 'Unknown'); ?></td>
                                    <td>★ <?php echo number_format($game['average_rating'] ?? 0, 1); ?></td>
                                    <td>
                                        <a href="<?= $baseUrl ?>/index.php?controller=game&action=show&id=<?php echo $game['game_id']; ?>" class="btn-view">View</a>
                                        <a href="<?= $baseUrl ?>/index.php?controller=game&action=edit&id=<?php echo $game['game_id']; ?>" class="btn-edit">Edit</a>
                                        <a href="<?= $baseUrl ?>/index.php?controller=game&action=delete&id=<?php echo $game['game_id']; ?>" 
                                           class="btn-delete"
                                           onclick="return confirm('Are you sure you want to delete this game?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Categories Management Section -->
        <div class="section">
            <div class="section-header">
                <h2>📁 Categories Management</h2>
                <a href="<?= $baseUrl ?>/index.php?controller=category&action=create" class="btn-primary">+ Add Category</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Games Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="4" class="empty-state">No categories found. <a href="<?= $baseUrl ?>/index.php?controller=category&action=create">Create your first category!</a></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $category): 
                                $gamesCount = 0;
                                foreach ($games as $game) {
                                    if ($game['category_id'] == $category['category_id']) {
                                        $gamesCount++;
                                    }
                                }
                            ?>
                                <tr>
                                    <td><?php echo $category['category_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                                    <td><?php echo $gamesCount; ?></td>
                                    <td>
                                        <a href="<?= $baseUrl ?>/index.php?controller=category&action=edit&id=<?php echo $category['category_id']; ?>" class="btn-edit">Edit</a>
                                        <a href="<?= $baseUrl ?>/index.php?controller=category&action=delete&id=<?php echo $category['category_id']; ?>" 
                                           class="btn-delete"
                                           onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="section">
            <div class="section-header">
                <h2>⭐ Reviews Management</h2>
                <span style="color: #888; font-size: 0.9rem;">Total: <?php echo count($reviews); ?> reviews</span>
            </div>
            <div class="search-filter-bar">
                <input type="text" id="reviewSearch" class="search-input" placeholder="Search reviews..." />
                <select id="ratingFilter" class="filter-select">
                    <option value="">All Ratings</option>
                    <option value="5">5 Stars</option>
                    <option value="4">4+ Stars</option>
                    <option value="3">3+ Stars</option>
                    <option value="2">2+ Stars</option>
                    <option value="1">1+ Stars</option>
                </select>
            </div>
            <div id="reviewsList">
                <?php if (empty($reviews)): ?>
                    <div class="empty-state">No reviews found.</div>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item" data-rating="<?php echo $review['rating']; ?>">
                            <div class="review-header">
                                <div>
                                    <strong><?php echo htmlspecialchars($review['game_title'] ?? 'Unknown Game'); ?></strong>
                                    <span style="color: #888; margin-left: 10px;">by <?php echo htmlspecialchars($review['user_name'] ?? 'Anonymous'); ?></span>
                                </div>
                                <span class="review-rating">★ <?php echo $review['rating']; ?>/5</span>
                            </div>
                            <div class="review-comment">
                                <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                            </div>
                            <div class="review-meta">
                                <?php echo date('F j, Y, g:i a', strtotime($review['created_at'])); ?>
                                <a href="<?= $baseUrl ?>/index.php?controller=review&action=delete&id=<?php echo $review['review_id']; ?>" 
                                   class="btn-delete"
                                   style="float: right;"
                                   onclick="return confirm('Are you sure you want to delete this review?')">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Review search and filter
        const reviewSearch = document.getElementById('reviewSearch');
        const ratingFilter = document.getElementById('ratingFilter');
        const reviewsList = document.getElementById('reviewsList');
        const reviewItems = reviewsList.querySelectorAll('.review-item');

        function filterReviews() {
            const searchTerm = reviewSearch.value.toLowerCase();
            const minRating = ratingFilter.value ? parseInt(ratingFilter.value) : 0;

            reviewItems.forEach(item => {
                const rating = parseInt(item.getAttribute('data-rating'));
                const text = item.textContent.toLowerCase();
                
                const matchesSearch = !searchTerm || text.includes(searchTerm);
                const matchesRating = rating >= minRating;
                
                if (matchesSearch && matchesRating) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        reviewSearch.addEventListener('input', filterReviews);
        ratingFilter.addEventListener('change', filterReviews);
    </script>
</body>
</html>

