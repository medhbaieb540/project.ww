<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> | GameBridge</title>
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
            <a href="<?php echo BASE_URL; ?>/index.php?controller=category&action=index">Categories Management</a>
        </nav>
    </header>

    <section>
        <div class="game-detail">
            <div class="game-detail-header">
                <a href="<?php echo BASE_URL; ?>/index.php?controller=category&action=index" class="btn-back">← Back to Categories</a>
                <div class="game-actions-header">
                    <a href="<?php echo BASE_URL; ?>/index.php?controller=category&action=edit&id=<?php echo $category['category_id']; ?>" class="btn-edit">Edit</a>
                    <a href="<?php echo BASE_URL; ?>/index.php?controller=category&action=delete&id=<?php echo $category['category_id']; ?>" 
                       class="btn-delete" 
                       onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                </div>
            </div>

            <div class="game-detail-content">
                <div class="game-detail-info">
                    <h1><?php echo htmlspecialchars($category['name']); ?></h1>
                    
                    <div class="game-meta">
                        <span class="meta-item">
                            <strong>Category ID:</strong> <?php echo $category['category_id']; ?>
                        </span>
                        <?php if (!empty($category['created_at'])): ?>
                            <span class="meta-item">
                                <strong>Created:</strong> <?php echo date('F j, Y', strtotime($category['created_at'])); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($category['description'])): ?>
                        <div class="game-description-full">
                            <h3>Description</h3>
                            <p><?php echo nl2br(htmlspecialchars($category['description'])); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="game-description-full">
                            <p style="color: #888;">No description available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

</body>
</html>

