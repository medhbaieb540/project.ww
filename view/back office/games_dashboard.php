<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../model/GameModel.php';
require_once __DIR__ . '/../../model/CategoryModel.php';
require_once __DIR__ . '/../../model/ReviewModel.php';

$gameModel = new GameModel();
$categoryModel = new CategoryModel();
$reviewModel = new ReviewModel();

$games = $gameModel->getAllGames();
$categories = $categoryModel->getAllCategories();
$reviews = $reviewModel->getAllReviews();
$stats = $gameModel->getGameStatistics();
$reviewStats = $reviewModel->getReviewStatistics();

$baseUrl = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GameBridge | Games Dashboard</title>
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/style.css">
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
      position: fixed;
      height: 100vh;
      overflow-y: auto;
    }

    .sidebar h2 {
      font-family: 'Orbitron', sans-serif;
      color: var(--accent);
      margin-bottom: 40px;
      text-align: center;
      font-size: 1.5rem;
    }

    .sidebar a {
      color: #bbb;
      text-decoration: none;
      padding: 12px 15px;
      margin-bottom: 8px;
      border-radius: 6px;
      transition: 0.3s;
      font-weight: 500;
      display: block;
    }

    .sidebar a:hover, .sidebar a.active {
      background: var(--accent);
      color: #000;
    }

    .sidebar .divider {
      margin: 20px 0;
      border-top: 1px solid #1aff8720;
      padding-top: 20px;
    }

    /* ===== Main Content ===== */
    .main-content {
      flex: 1;
      padding: 40px 60px;
      overflow-y: auto;
      margin-left: 250px;
    }

    .main-content h1 {
      font-family: 'Orbitron', sans-serif;
      color: var(--accent);
      margin-bottom: 40px;
      text-align: center;
      font-size: 2rem;
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
      font-size: 2rem;
      margin-bottom: 8px;
    }

    .stat-card p {
      color: #ccc;
      font-size: 1rem;
    }

    /* ===== Section Titles ===== */
    h2.section-title {
      font-family: 'Orbitron', sans-serif;
      color: var(--accent);
      margin: 40px 0 20px 0;
      border-left: 4px solid var(--accent);
      padding-left: 15px;
      font-size: 1.3rem;
    }

    /* ===== Search and Filter Bar ===== */
    .search-filter-bar {
      display: flex;
      gap: 15px;
      margin-bottom: 25px;
      flex-wrap: wrap;
      align-items: center;
    }

    .search-input {
      flex: 1;
      min-width: 250px;
      padding: 12px 18px;
      background: #0c0c0c;
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
      padding: 12px 18px;
      background: #0c0c0c;
      border: 1px solid #1aff8715;
      border-radius: 6px;
      color: var(--accent);
      font-size: 0.95rem;
      min-width: 150px;
      font-family: 'Poppins', sans-serif;
      cursor: pointer;
    }

    .filter-select:focus {
      outline: none;
      border-color: var(--accent);
    }

    .btn-search, .btn-filter {
      padding: 12px 25px;
      background: var(--accent);
      color: #000;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
      font-family: 'Poppins', sans-serif;
    }

    .btn-search:hover, .btn-filter:hover {
      background: #11cc66;
      transform: translateY(-2px);
    }

    .btn-add {
      padding: 12px 25px;
      background: var(--accent);
      color: #000;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      display: inline-block;
      transition: 0.3s;
      margin-bottom: 20px;
    }

    .btn-add:hover {
      background: #11cc66;
      transform: translateY(-2px);
    }

    /* ===== Tables ===== */
    .table-container {
      background: var(--bg-card);
      border-radius: 10px;
      overflow: hidden;
      margin-bottom: 40px;
      border: 1px solid #1aff8720;
    }

    table {
      width: 100%;
      border-collapse: collapse;
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
      font-family: 'Orbitron', sans-serif;
      font-weight: 600;
    }

    tr:nth-child(even) {
      background: #121212;
    }

    tr:hover {
      background: #1aff870f;
    }

    td {
      color: #ccc;
    }

    .btn-action {
      background: transparent;
      border: 2px solid;
      border-radius: 6px;
      padding: 6px 12px;
      font-size: 0.8rem;
      cursor: pointer;
      transition: 0.3s;
      text-transform: uppercase;
      font-weight: 600;
      margin-right: 8px;
      text-decoration: none;
      display: inline-block;
    }

    .btn-edit {
      border-color: var(--accent);
      color: var(--accent);
    }

    .btn-edit:hover {
      background: var(--accent);
      color: #000;
    }

    .btn-delete {
      border-color: #ff3366;
      color: #ff3366;
    }

    .btn-delete:hover {
      background: #ff3366;
      color: #fff;
    }

    .btn-view {
      border-color: #5b4bff;
      color: #5b4bff;
    }

    .btn-view:hover {
      background: #5b4bff;
      color: #fff;
    }

    .rating-badge {
      color: #ffaa00;
      font-weight: 600;
    }

    .empty-state {
      text-align: center;
      padding: 40px;
      color: #888;
    }

    /* ===== Reviews Section ===== */
    .reviews-container {
      background: var(--bg-card);
      border-radius: 10px;
      padding: 25px;
      margin-bottom: 40px;
      border: 1px solid #1aff8720;
    }

    .review-item {
      background: #0c0c0c;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 15px;
      border: 1px solid #333;
      transition: 0.3s;
    }

    .review-item:hover {
      border-color: var(--accent);
      background: #0f0f0f;
    }

    .review-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .review-header strong {
      color: var(--accent);
      font-size: 1.1rem;
    }

    .review-rating {
      color: #ffaa00;
      font-weight: 600;
      font-size: 1rem;
    }

    .review-comment {
      color: #ccc;
      line-height: 1.6;
      margin-bottom: 10px;
    }

    .review-meta {
      color: #888;
      font-size: 0.85rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
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
      .main-content {
        margin-left: 0;
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <!-- ===== Sidebar ===== -->
  <div class="sidebar">
    <h2>Admin</h2>
    <a href="#" onclick="return false;">Dashboard</a>
    <a href="#" onclick="return false;">Users</a>
    <a href="<?= $baseUrl ?>/index.php?controller=game&action=gamesDashboard" class="active">Games</a>
    <a href="#" onclick="return false;">Tournaments</a>
    <a href="#" onclick="return false;">Feedback</a>
    <a href="#" onclick="return false;">Rewards</a>
    <div class="divider"></div>
    <a href="<?= $baseUrl ?>/assets/index.html">← Back to Home</a>
  </div>

  <!-- ===== Main Content ===== -->
  <div class="main-content">
    <h1>🎮 GameBridge Games Dashboard</h1>

    <!-- ===== Stats Overview ===== -->
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

    <!-- ===== Games Management ===== -->
    <h2 class="section-title">| Manage Games</h2>
    <a href="<?= $baseUrl ?>/index.php?controller=game&action=create" class="btn-add">+ Add New Game</a>
    
    <div class="search-filter-bar">
      <input type="text" id="gameSearch" class="search-input" placeholder="Search games by title or description..." />
      <select id="categoryFilter" class="filter-select">
        <option value="">All Categories</option>
        <?php foreach ($categories as $category): ?>
          <option value="<?php echo $category['category_id']; ?>">
            <?php echo htmlspecialchars($category['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button onclick="searchGames()" class="btn-search">Search</button>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Developer</th>
            <th>Rating</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($games)): ?>
            <tr>
              <td colspan="6" class="empty-state">No games found. <a href="<?= $baseUrl ?>/index.php?controller=game&action=create" style="color: var(--accent);">Create your first game!</a></td>
            </tr>
          <?php else: ?>
            <?php foreach ($games as $game): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($game['title']); ?></strong></td>
                <td><?php echo htmlspecialchars($game['category_name'] ?? 'Uncategorized'); ?></td>
                <td><?php echo htmlspecialchars($game['developer_name'] ?? 'Unknown'); ?></td>
                <td><span class="rating-badge">★ <?php echo number_format($game['average_rating'] ?? 0, 1); ?></span></td>
                <td><?php echo date('Y-m-d', strtotime($game['created_at'])); ?></td>
                <td>
                  <a href="<?= $baseUrl ?>/index.php?controller=game&action=show&id=<?php echo $game['game_id']; ?>" class="btn-action btn-view">View</a>
                  <a href="<?= $baseUrl ?>/index.php?controller=game&action=edit&id=<?php echo $game['game_id']; ?>" class="btn-action btn-edit">Edit</a>
                  <a href="<?= $baseUrl ?>/index.php?controller=game&action=delete&id=<?php echo $game['game_id']; ?>" 
                     class="btn-action btn-delete"
                     onclick="return confirm('Are you sure you want to delete this game?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- ===== Categories Management ===== -->
    <h2 class="section-title">| Manage Categories</h2>
    <a href="<?= $baseUrl ?>/index.php?controller=category&action=create" class="btn-add">+ Add New Category</a>

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
              <td colspan="4" class="empty-state">No categories found. <a href="<?= $baseUrl ?>/index.php?controller=category&action=create" style="color: var(--accent);">Create your first category!</a></td>
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
                  <a href="<?= $baseUrl ?>/index.php?controller=category&action=edit&id=<?php echo $category['category_id']; ?>" class="btn-action btn-edit">Edit</a>
                  <a href="<?= $baseUrl ?>/index.php?controller=category&action=delete&id=<?php echo $category['category_id']; ?>" 
                     class="btn-action btn-delete"
                     onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- ===== Reviews Management ===== -->
    <h2 class="section-title">| Recent Reviews</h2>
    
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
      <button onclick="filterReviews()" class="btn-filter">Filter</button>
    </div>

    <div class="reviews-container">
      <?php if (empty($reviews)): ?>
        <div class="empty-state">No reviews found.</div>
      <?php else: ?>
        <?php 
        $displayReviews = array_slice($reviews, 0, 10); // Show latest 10 reviews
        foreach ($displayReviews as $review): 
        ?>
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
              <span><?php echo date('F j, Y, g:i a', strtotime($review['created_at'])); ?></span>
              <a href="<?= $baseUrl ?>/index.php?controller=review&action=delete&id=<?php echo $review['review_id']; ?>" 
                 class="btn-action btn-delete"
                 onclick="return confirm('Are you sure you want to delete this review?')">Delete</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <footer>© 2025 GameBridge • Games Management Dashboard</footer>
  </div>

  <script>
    function searchGames() {
      const searchTerm = document.getElementById('gameSearch').value.trim();
      const categoryId = document.getElementById('categoryFilter').value;
      
      let url = '<?= $baseUrl ?>/index.php?controller=game&action=';
      
      if (searchTerm) {
        url += 'search&q=' + encodeURIComponent(searchTerm);
      } else if (categoryId) {
        url += 'filter&category_id=' + categoryId;
      } else {
        url += 'index';
      }
      
      window.location.href = url;
    }

    document.getElementById('gameSearch').addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        searchGames();
      }
    });

    function filterReviews() {
      const searchTerm = document.getElementById('reviewSearch').value.toLowerCase();
      const minRating = document.getElementById('ratingFilter').value ? parseInt(document.getElementById('ratingFilter').value) : 0;
      const reviewItems = document.querySelectorAll('.review-item');

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

    document.getElementById('reviewSearch').addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        filterReviews();
      }
    });

    document.getElementById('ratingFilter').addEventListener('change', filterReviews);
  </script>
</body>
</html>

