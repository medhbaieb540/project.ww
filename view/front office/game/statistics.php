<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>GameBridge | Game Statistics</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/style.css" />
    <style>
        .stats-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .stat-card {
            background: var(--bg-card);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #1aff8720;
            text-align: center;
        }
        .stat-card h3 {
            color: var(--accent);
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .stat-card p {
            color: #888;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo" />
            <span>GameBridge</span>
        </div>
        <nav>
            <a href="<?php echo BASE_URL; ?>/assets/index.html">Home</a>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=game&action=index">Games</a>
        </nav>
    </header>

    <section class="stats-container">
        <h2>Game Statistics</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $stats['total_games'] ?? 0; ?></h3>
                <p>Total Games</p>
            </div>
            <div class="stat-card">
                <h3>★ <?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></h3>
                <p>Average Rating</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_categories'] ?? 0; ?></h3>
                <p>Categories</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_developers'] ?? 0; ?></h3>
                <p>Developers</p>
            </div>
        </div>

        <div style="margin-top: 30px;">
            <a href="<?php echo BASE_URL; ?>/index.php?controller=game&action=index" 
               style="padding: 10px 20px; background: var(--accent); color: #000; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">
               Back to Games
            </a>
        </div>
    </section>
</body>
</html>

