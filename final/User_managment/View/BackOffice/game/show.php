<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['title']); ?> | GameBridge</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo">
        </div>
        <nav>
            <a href="<?php echo BASE_URL; ?>/assets/index.html">Home</a>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=game&action=index">Games Management</a>
        </nav>
    </header>

    <section>
        <div class="game-detail">
            <div class="game-detail-header">
                <a href="<?php echo BASE_URL; ?>/index.php?controller=game&action=index" class="btn-back" style="color: var(--accent); text-decoration: none; margin-bottom: 20px; display: inline-block;">← Back to Games</a>
                <div class="game-actions-header" style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <a href="<?php echo BASE_URL; ?>/index.php?controller=game&action=edit&id=<?php echo $game['game_id']; ?>" class="btn-edit" style="padding: 10px 20px; background: #5b4bff; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600;">Edit</a>
                    <a href="<?php echo BASE_URL; ?>/index.php?controller=game&action=delete&id=<?php echo $game['game_id']; ?>" 
                       class="btn-delete" 
                       style="padding: 10px 20px; background: #ff3366; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600;"
                       onclick="return confirm('Are you sure you want to delete this game?');">Delete</a>
                </div>
            </div>

            <div class="game-detail-content">
                <?php if (!empty($game['image_path'])): ?>
                    <div class="game-detail-image">
                        <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($game['image_path']); ?>"
                             alt="<?php echo htmlspecialchars($game['title']); ?>"
                             style="width: 100%; max-width: 500px; border-radius: 12px;"
                             onerror="this.src='<?php echo BASE_URL; ?>/assets/images/game1.jpg'">
                    </div>
                <?php endif; ?>

                <div class="game-detail-info">
                    <h1><?php echo htmlspecialchars($game['title']); ?></h1>
                    
                    <div class="game-meta">
                        <span class="meta-item">
                            <strong>Developer:</strong> <?php echo htmlspecialchars($game['developer_name'] ?? 'Unknown'); ?>
                        </span>
                        <span class="meta-item">
                            <strong>Category:</strong> <?php echo htmlspecialchars($game['category_name'] ?? 'Uncategorized'); ?>
                        </span>
                        <span class="meta-item">
                            <strong>Rating:</strong> ★ <?php echo number_format($game['average_rating'] ?? 0, 1); ?>
                        </span>
                        <span class="meta-item">
                            <strong>Created:</strong> <?php echo date('F j, Y', strtotime($game['created_at'])); ?>
                        </span>
                    </div>

                    <div class="game-description-full">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($game['description'])); ?></p>
                    </div>

                    <?php if (!empty($game['file_path'])): ?>
                        <div class="game-download" style="margin-top: 20px;">
                            <a href="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($game['file_path']); ?>" 
                               class="btn-download" 
                               style="padding: 12px 30px; background: var(--accent); color: #000; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;"
                               download>Download Game</a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Reviews Section -->
                    <div class="reviews-section" style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #333;">
                        <h3 style="color: var(--accent); margin-bottom: 20px;">Reviews</h3>
                        <a href="<?php echo BASE_URL; ?>/index.php?controller=review&action=create&game_id=<?php echo $game['game_id']; ?>" 
                           style="padding: 10px 20px; background: var(--accent); color: #000; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block; margin-bottom: 20px;">
                           Add Review
                        </a>
                        
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item" style="background: var(--bg-card); padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #333;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                        <strong style="color: var(--accent);"><?php echo htmlspecialchars($review['user_name'] ?? 'Anonymous'); ?></strong>
                                        <span style="color: #ffaa00;">★ <?php echo $review['rating']; ?>/5</span>
                                    </div>
                                    <p style="color: #ccc; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                    <small style="color: #888; font-size: 12px;">
                                        <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #888;">No reviews yet. Be the first to review this game!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

</body>
</html>

