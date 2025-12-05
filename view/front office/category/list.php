<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameBridge | Categories Management</title>
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
            <a href="<?php echo BASE_URL; ?>/index.php?controller=category&action=index" class="active">Categories Management</a>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=category&action=create" class="btn-primary">Add New Category</a>
        </nav>
    </header>

    <section>
        <h2>Categories Management</h2>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Categories Table -->
        <div class="games-grid" id="categoriesGrid">
            <?php if (empty($categories)): ?>
                <div class="no-games">
                    <p>No categories found. <a href="<?php echo BASE_URL; ?>/index.php?controller=category&action=create">Create your first category!</a></p>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <div class="game-card">
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <?php if (!empty($category['description'])): ?>
                            <p class="game-description">
                                <?php echo htmlspecialchars($category['description']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="game-actions">
                            <a href="<?php echo BASE_URL; ?>/index.php?controller=category&action=show&id=<?php echo $category['category_id']; ?>" class="btn-view">View</a>
                            <a href="<?php echo BASE_URL; ?>/index.php?controller=category&action=edit&id=<?php echo $category['category_id']; ?>" class="btn-edit">Edit</a>
                            <a href="<?php echo BASE_URL; ?>/index.php?controller=category&action=delete&id=<?php echo $category['category_id']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

</body>
</html>

