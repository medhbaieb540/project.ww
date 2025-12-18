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
    <title>GameBridge | Add Review</title>
    <link rel="stylesheet" href="../../public/css/style.css" />
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
            background: var(--bg-dark);
            color: var(--text);
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: #000;
            border-bottom: 1px solid var(--accent);
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-container img {
            width: 45px;
            height: 45px;
            border-radius: 8px;
        }
        
        nav {
            display: flex;
            gap: 1px;
            margin-left: auto;
            align-items: center;
        }
        
        nav a {
            color: var(--text);
            text-decoration: none;
            margin: 0 12px;
            font-weight: 500;
            transition: 0.3s;
        }
        
        nav a:hover {
            color: var(--accent);
        }
        
        section {
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: var(--bg-card);
            border-radius: 12px;
            border: 1px solid #1aff8720;
        }
        
        .form-container h2 {
            color: var(--accent);
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--accent);
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            background: #0c0c0c;
            border: 1px solid #333;
            border-radius: 6px;
            color: var(--text);
            font-family: 'Poppins', sans-serif;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 10px #1aff8722;
        }
        
        .rating-input {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .rating-input label {
            margin: 0;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }
        
        .rating-input input[type="radio"] {
            width: auto;
            margin: 0;
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
        
        .alert-info {
            background: #5b4bff20;
            border: 1px solid #5b4bff;
            color: #5b4bff;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-submit {
            padding: 12px 30px;
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            flex: 1;
        }
        
        .btn-submit:hover {
            background: #11cc66;
            transform: translateY(-2px);
        }
        
        .btn-cancel {
            padding: 12px 30px;
            background: transparent;
            color: var(--text);
            border: 1px solid #333;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            transition: 0.3s;
            flex: 1;
            text-align: center;
        }
        
        .btn-cancel:hover {
            border-color: var(--accent);
            background: #1aff8720;
        }
    </style>
</head>
<body>

    <section>
        <div class="form-container">
            <h2>Add Review for <?php echo htmlspecialchars($game['title']); ?></h2>
            
            <?php if (isset($existingReview) && $existingReview): ?>
                <div class="alert alert-info">
                    ⚠️ You have already reviewed this game. Submitting a new review will update your existing review.
                </div>
            <?php endif; ?>
            
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
            
            <form id="reviewForm" action="review.php?action=store" method="POST">
                <input type="hidden" name="game_id" value="<?php echo $game['game_id']; ?>" />
                
                <div class="form-group">
                    <label>Rating *</label>
                    <div class="rating-input">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <label>
                                <input type="radio" name="rating" value="<?php echo $i; ?>" required />
                                <?php echo $i; ?> ★
                            </label>
                        <?php endfor; ?>
                    </div>
                    <div class="error-message" id="ratingError"></div>
                </div>
                
                <div class="form-group">
                    <label for="comment">Comment *</label>
                    <textarea id="comment" name="comment" placeholder="Write your review (10-1000 characters)" 
                              rows="6" minlength="10" maxlength="1000" required></textarea>
                    <div class="error-message" id="commentError"></div>
                    <div class="char-count" id="commentCount">0 / 1000</div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Submit Review</button>
                    <a href="../../public/index.php?controller=game&action=show&id=<?php echo $game['game_id']; ?>" 
                       class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </section>

    <script>
        const commentInput = document.getElementById('comment');
        const commentCount = document.getElementById('commentCount');
        const commentError = document.getElementById('commentError');

        commentInput.addEventListener('input', function() {
            const length = this.value.length;
            commentCount.textContent = length + ' / 1000';
            
            if (length < 10) {
                commentError.textContent = 'Comment must be at least 10 characters long';
                commentError.classList.add('show');
            } else if (length > 1000) {
                commentError.textContent = 'Comment must not exceed 1000 characters';
                commentError.classList.add('show');
            } else {
                commentError.classList.remove('show');
            }
        });

        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            const rating = document.querySelector('input[name="rating"]:checked');
            const comment = commentInput.value.trim();

            if (!rating) {
                e.preventDefault();
                document.getElementById('ratingError').textContent = 'Please select a rating';
                document.getElementById('ratingError').classList.add('show');
            }

            if (comment.length < 10 || comment.length > 1000) {
                e.preventDefault();
                alert('Please fix the errors before submitting');
            }
        });
    </script>
</body>
</html>
