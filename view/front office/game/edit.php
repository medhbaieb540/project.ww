<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Game | GameBridge</title>
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
        <h2>Edit Game</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="<?php echo BASE_URL; ?>/index.php?controller=game&action=update" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $game['game_id']; ?>">

                <div class="form-group">
                    <label for="title">Game Title *</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo htmlspecialchars($game['title']); ?>"
                           placeholder="Enter game title">
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="5" required 
                              placeholder="Enter game description"><?php echo htmlspecialchars($game['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>"
                                    <?php echo $category['category_id'] == $game['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (!empty($game['image_path'])): ?>
                    <div class="form-group">
                        <label>Current Image</label>
                        <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($game['image_path']); ?>"
                             alt="Current image" class="current-image"
                             onerror="this.src='<?php echo BASE_URL; ?>/assets/images/game1.jpg'">
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="image">Update Game Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Leave empty to keep current image. Accepted formats: JPG, PNG, GIF</small>
                </div>

                <?php if (!empty($game['file_path'])): ?>
                    <div class="form-group">
                        <label>Current Game File</label>
                        <p class="current-file"><?php echo htmlspecialchars(basename($game['file_path'])); ?></p>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="game_file">Update Game File</label>
                    <input type="file" id="game_file" name="game_file" accept=".zip,.rar,.exe,.apk">
                    <small>Leave empty to keep current file. Accepted formats: ZIP, RAR, EXE, APK</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Update Game</button>
                    <a href="<?php echo BASE_URL; ?>/index.php?controller=game&action=index" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </section>

</body>
</html>

