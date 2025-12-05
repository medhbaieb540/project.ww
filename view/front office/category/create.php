<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Category | GameBridge</title>
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
        <h2>Create New Category</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="<?php echo BASE_URL; ?>/index.php?controller=category&action=store" method="POST">
                <div class="form-group">
                    <label for="name">Category Name *</label>
                    <input type="text" id="name" name="name" required 
                           placeholder="Enter category name">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5" 
                              placeholder="Enter category description (optional)"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Create Category</button>
                    <a href="<?php echo BASE_URL; ?>/index.php?controller=category&action=index" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </section>

</body>
</html>

