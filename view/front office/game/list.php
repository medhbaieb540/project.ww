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
        .export-btn {
            padding: 8px 15px;
            background: #5b4bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: 0.3s;
        }
        .export-btn:hover {
            background: #4a3aee;
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

        <div class="advanced-controls">
            <div class="controls-header">
                <div>
                    <a href="<?= $baseUrl ?>/index.php?controller=game&action=create" class="upload-btn">+ Upload New Game</a>
                    <a href="<?= $baseUrl ?>/index.php?controller=game&action=export" class="export-btn" style="margin-left: 10px; padding: 8px 15px;">📥 Export Games</a>
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
                    <option value="export">Export Selected</option>
                    <option value="delete">Delete Selected</option>
                </select>
                <button onclick="executeBulkAction()">Apply</button>
            </div>
        </div>
        
        <div class="stats-bar">
            <span>📊 Total Games: <strong><?php echo count($games); ?></strong></span>
            <span id="filteredCount">Showing: <strong><?php echo count($games); ?></strong> games</span>
        </div>

        <!-- Grid View -->
        <div class="games-grid games-grid-view active" id="gridView">
            <?php if (empty($games)): ?>
                <div class="no-games">
                    <p>No games found. <a href="<?= $baseUrl ?>/index.php?controller=game&action=create">Create your first game!</a></p>
                </div>
            <?php else: ?>
                <?php foreach ($games as $game): ?>
                    <div class="game-card" data-game-id="<?php echo $game['game_id']; ?>" data-created-date="<?php echo $game['created_at']; ?>">
                        <input type="checkbox" class="game-checkbox" value="<?php echo $game['game_id']; ?>" onchange="updateBulkActions()">
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
        
        <!-- List View -->
        <div class="games-list-view" id="listView">
            <?php if (empty($games)): ?>
                <div class="no-games">
                    <p>No games found. <a href="<?= $baseUrl ?>/index.php?controller=game&action=create">Create your first game!</a></p>
                </div>
            <?php else: ?>
                <?php foreach ($games as $game): ?>
                    <div class="list-item" data-game-id="<?php echo $game['game_id']; ?>" data-created-date="<?php echo $game['created_at']; ?>">
                        <input type="checkbox" class="game-checkbox" value="<?php echo $game['game_id']; ?>" onchange="updateBulkActions()">
                        <?php if (!empty($game['image_path'])): ?>
                            <img src="<?= $baseUrl ?>/<?php echo htmlspecialchars($game['image_path']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" />
                        <?php else: ?>
                            <img src="<?= $baseUrl ?>/assets/images/game1.jpg" alt="No Image" />
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
                                <a href="<?= $baseUrl ?>/<?php echo htmlspecialchars($game['file_path']); ?>" class="btn-play" download>PLAY</a>
                            <?php else: ?>
                                <a href="#" class="btn-play" onclick="alert('Game file not available'); return false;">PLAY</a>
                            <?php endif; ?>
                            <a href="<?= $baseUrl ?>/index.php?controller=review&action=create&game_id=<?php echo $game['game_id']; ?>" class="btn-review">REVIEW</a>
                            <a href="<?= $baseUrl ?>/index.php?controller=game&action=show&id=<?php echo $game['game_id']; ?>" class="btn-review" style="background: #5b4bff; border-color: #5b4bff; color: #fff;">VIEW</a>
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
                        window.location.href = '<?= $baseUrl ?>/index.php?controller=game&action=delete&id=' + id;
                    });
                }
            } else if (action === 'export') {
                // Export functionality
                exportSelectedGames(selected);
            }
        }
        
        function exportSelectedGames(ids) {
            // Create CSV export
            const games = [];
            ids.forEach(id => {
                const gameCard = document.querySelector(`[data-game-id="${id}"]`);
                if (gameCard) {
                    const title = gameCard.querySelector('h3')?.textContent || '';
                    const info = gameCard.querySelector('.game-info')?.textContent || '';
                    games.push({
                        title: title,
                        info: info
                    });
                }
            });
            
            let csv = 'Title,Category,Developer,Rating\n';
            games.forEach(game => {
                csv += `"${game.title}","${game.info}"\n`;
            });
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'games_export_' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
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
    </script>
</body>
</html>
