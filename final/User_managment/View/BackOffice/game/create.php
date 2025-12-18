<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GameBridge | Upload New Game</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Orbitron:wght@400;700;900&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --bg-dark: #0a0a0a;
            --bg-card: #111;
            --text: #e0e0e0;
            --accent: #1aff87;
        }
        
        html, body {
            width: 100%;
            height: 100%;
            background: var(--bg-dark);
            color: var(--text);
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
        }
        
        header {
            background: rgba(0, 0, 0, 0.9);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #1aff8730;
            width: 100%;
            position: relative;
            z-index: 100;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
            min-width: 150px;
        }
        
        .logo-container img {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            border: 2px solid var(--accent);
        }
        
        .logo-text {
            font-family: 'Orbitron', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--accent);
        }
        
        nav {
            display: flex;
            gap: 30px;
            flex: 1;
            margin-left: 40px;
        }
        
        nav a {
            color: var(--text);
            text-decoration: none;
            transition: all 0.3s;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        nav a:hover {
            color: var(--accent);
            background: #1aff8715;
        }
        
        nav a.active {
            color: var(--accent);
            border-bottom: 2px solid var(--accent);
        }
        
        main {
            width: 100%;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }
        
        .upload-section {
            width: 100%;
            max-width: 700px;
        }
        
        .upload-title {
            color: var(--accent);
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 40px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .upload-form {
            background: var(--bg-card);
            padding: 40px;
            border-radius: 12px;
            border: 1px solid #1aff8730;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: var(--accent);
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            background: #0c0c0c;
            border: 2px solid #1aff8730;
            border-radius: 8px;
            color: var(--text);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 15px rgba(26, 255, 135, 0.3);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .file-upload-wrapper {
            position: relative;
        }
        
        .file-upload-btn {
            padding: 12px 24px;
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            margin-right: 10px;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .file-upload-btn:hover {
            background: #11cc66;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 255, 135, 0.3);
        }
        
        .file-upload-info {
            color: #888;
            font-size: 13px;
            margin-top: 8px;
        }
        
        .upload-submit-btn {
            width: 100%;
            padding: 15px;
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 30px;
        }
        
        .upload-submit-btn:hover {
            background: #11cc66;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(26, 255, 135, 0.4);
        }
        
        .error-message {
            color: #ff3366;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }
        
        .error-message.show {
            display: block;
        }
        
        .char-count {
            text-align: right;
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-error {
            background: #ff336620;
            border: 1px solid #ff3366;
            color: #ff3366;
        }
        
        .alert-success {
            background: #1aff8720;
            border: 1px solid #1aff87;
            color: #1aff87;
        }
    </style>
</head>
<body>
    <main>
        <div class="upload-section">
            <h1 class="upload-title">Upload New Game</h1>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <form id="gameForm" action="../../View/FrontOffice/list.php?controller=game&action=store" method="POST" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <label for="title">Game title</label>
                <input type="text" id="title" name="title" placeholder="Game title" 
                       minlength="3" maxlength="100" required />
                <div class="error-message" id="titleError"></div>
                <div class="char-count" id="titleCount">0 / 100</div>
            </div>
            
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="error-message" id="categoryError"></div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Description" 
                          rows="6" minlength="20" maxlength="2000" required></textarea>
                <div class="error-message" id="descriptionError"></div>
                <div class="char-count" id="descriptionCount">0 / 2000</div>
            </div>
            
            <div class="form-group">
                <label for="image">Game Image</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/jpg,image/png,image/gif" required />
                <div class="file-upload-info">Supported: JPG, PNG, GIF (Max 5MB)</div>
                <div class="error-message" id="imageError"></div>
            </div>
            
            <div class="form-group">
                <label>File Upload</label>
                <div class="file-upload-wrapper">
                    <input type="file" id="game_file" name="game_file" accept=".zip,.rar,.exe,.apk" required 
                           style="padding: 8px; border: 2px solid var(--accent);" />
                    <div class="file-upload-info">Supported: ZIP, RAR, EXE, APK (Max 500MB)</div>
                </div>
                <div class="error-message" id="fileError"></div>
            </div>
            
            <button type="submit" class="upload-submit-btn">UPLOAD GAME</button>
            </form>
        </div>
    </main>

    <script>
        // Title validation
        const titleInput = document.getElementById('title');
        const titleCount = document.getElementById('titleCount');
        const titleError = document.getElementById('titleError');

        function validateTitle() {
            const value = titleInput.value.trim();
            const length = value.length;
            titleCount.textContent = length + ' / 100';
            
            if (length === 0) {
                titleError.textContent = 'Title is required and cannot be empty';
                titleError.classList.add('show');
                return false;
            } else if (length < 3) {
                titleError.textContent = 'Title must be at least 3 characters';
                titleError.classList.add('show');
                return false;
            } else if (length > 100) {
                titleError.textContent = 'Title must not exceed 100 characters';
                titleError.classList.add('show');
                return false;
            } else if (!/^[a-zA-Z0-9\s\-_.,!?()]+$/.test(value)) {
                titleError.textContent = 'Invalid characters';
                titleError.classList.add('show');
                return false;
            } else {
                titleError.classList.remove('show');
                return true;
            }
        }

        titleInput.addEventListener('input', validateTitle);
        titleInput.addEventListener('blur', validateTitle);

        // Description validation
        const descInput = document.getElementById('description');
        const descCount = document.getElementById('descriptionCount');
        const descError = document.getElementById('descriptionError');

        function validateDescription() {
            const value = descInput.value.trim();
            const length = value.length;
            descCount.textContent = length + ' / 2000';
            
            if (length === 0) {
                descError.textContent = 'Description is required and cannot be empty';
                descError.classList.add('show');
                return false;
            } else if (length < 20) {
                descError.textContent = 'Description must be at least 20 characters';
                descError.classList.add('show');
                return false;
            } else if (length > 2000) {
                descError.textContent = 'Description must not exceed 2000 characters';
                descError.classList.add('show');
                return false;
            } else {
                descError.classList.remove('show');
                return true;
            }
        }

        descInput.addEventListener('input', validateDescription);
        descInput.addEventListener('blur', validateDescription);

        // Image validation
        const imageInput = document.getElementById('image');
        const imageError = document.getElementById('imageError');

        function validateImage() {
            const file = imageInput.files[0];
            if (!file) {
                imageError.textContent = 'Please select a game image';
                imageError.classList.add('show');
                return false;
            }

            if (file.size > 5 * 1024 * 1024) {
                imageError.textContent = 'Image size must not exceed 5MB';
                imageError.classList.add('show');
                imageInput.value = '';
                return false;
            } else {
                imageError.classList.remove('show');
                return true;
            }
        }

        imageInput.addEventListener('change', validateImage);

        // File validation
        const fileInput = document.getElementById('game_file');
        const fileError = document.getElementById('fileError');

        function validateFile() {
            const file = fileInput.files[0];
            if (!file) {
                fileError.textContent = 'Please select a game file';
                fileError.classList.add('show');
                return false;
            }

            if (file.size > 500 * 1024 * 1024) {
                fileError.textContent = 'File size must not exceed 500MB';
                fileError.classList.add('show');
                fileInput.value = '';
                return false;
            } else {
                fileError.classList.remove('show');
                return true;
            }
        }

        fileInput.addEventListener('change', validateFile);

        // Category validation
        const categorySelect = document.getElementById('category_id');
        const categoryError = document.getElementById('categoryError');

        function validateCategory() {
            if (!categorySelect.value) {
                categoryError.textContent = 'Please select a category';
                categoryError.classList.add('show');
                return false;
            } else {
                categoryError.classList.remove('show');
                return true;
            }
        }

        categorySelect.addEventListener('change', validateCategory);

        // Form submission
        document.getElementById('gameForm').addEventListener('submit', function(e) {
            let isValid = true;
            let errorMessages = [];

            // Validate all fields
            if (!validateTitle()) {
                isValid = false;
                errorMessages.push('Title: ' + titleError.textContent);
            }

            if (!validateDescription()) {
                isValid = false;
                errorMessages.push('Description: ' + descError.textContent);
            }

            if (!validateCategory()) {
                isValid = false;
                errorMessages.push('Category: ' + categoryError.textContent);
            }

            if (!validateImage()) {
                isValid = false;
                errorMessages.push('Image: ' + imageError.textContent);
            }

            if (!validateFile()) {
                isValid = false;
                errorMessages.push('Game File: ' + fileError.textContent);
            }

            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = document.querySelector('.error-message.show');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                alert('Please fix the following errors before submitting:\n\n' + errorMessages.join('\n'));
            }
        });
    </script>
</body>
</html>
