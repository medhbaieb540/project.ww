<?php
// Use a root-wide session cookie so AJAX endpoints share the session
session_set_cookie_params(['path' => '/', 'httponly' => true]);
session_start();

$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
if ($userId === null) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../Controller/CommunityController.php';

$controller = new CommunityController($pdo);
$username = $_SESSION['username'] ?? 'Player';
$userRole = $_SESSION['user_role'] ?? ($_SESSION['role'] ?? 'player');

$selectedCategory = $_GET['category'] ?? '';
if ($selectedCategory !== '') {
    $posts = $controller->getPostsByCategory($selectedCategory);
} else {
    $posts = $controller->getAllPosts();
}

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GameBridge | Community</title>
  <link rel="stylesheet" href="../../public/css/stylecommunity.css">
  <link rel="stylesheet" href="../../public/css/stylefeedback.css">
  <style>
@import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@500&family=Poppins:wght@300;400;600&display=swap');

:root{
  --accent:#1AFF87;
  --bg-dark:#0c0c0c;
  --bg-card:#161616;
  --text:#f1f1f1;
  --border:rgba(26,255,135,0.12);
  --muted:#aaa;
}

/* Reset */
*{ margin:0; padding:0; box-sizing:border-box; }

body{
  background:var(--bg-dark);
  color:var(--text);
  font-family:'Poppins',sans-serif;
}

/* ===== HEADER (new style) ===== */
header{
  background:#000;
  padding:15px 40px;
  border-bottom:1px solid var(--accent);
  display:flex;
  justify-content:space-between;
  align-items:center;
}

header h1{
  font-family:'Orbitron',sans-serif;
  color:var(--accent);
  letter-spacing:2px;
}

nav{
  display:flex;
  align-items:center;
  gap:18px;
}

nav a{
  color:var(--text);
  text-decoration:none;
  font-weight:500;
  transition:.3s;
}

nav a:hover,
nav a.active{
  color:var(--accent);
}

/* Badge */
.user-badge{
  border:1px solid var(--accent);
  padding:6px 14px;
  border-radius:20px;
  color:var(--accent);
  font-size:13px;
}

/* submenu */
.submenu{
  display:none;
  padding:12px 10%;
  background:#0f0f0f;
  border-bottom:1px solid var(--border);
  gap:10px;
  flex-wrap:wrap;
}
.submenu.show{ display:flex; }

.submenu a{
  color:var(--accent);
  background:#111;
  border:1px solid var(--border);
  padding:8px 14px;
  border-radius:10px;
  text-decoration:none;
  font-weight:700;
  transition:.25s;
}
.submenu a:hover{
  background:var(--accent);
  color:#000;
  border-color:var(--accent);
}

/* search bar */
.search-bar{
  padding:20px 10%;
  display:flex;
  gap:10px;
  justify-content:flex-end;
  flex-wrap:wrap;
}

.search-bar input,
.search-bar select{
  padding:10px 12px;
  background:#111;
  color:var(--text);
  border:1px solid var(--border);
  border-radius:10px;
}

/* quick links */
.quick-links{
  padding:12px 10%;
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  align-items:center;
}
.quick-links .pill{ color:#bbb; font-size:.95rem; }

/* cards */
.card{
  background:var(--bg-card);
  border:1px solid var(--border);
  border-radius:12px;
  padding:20px;
  margin:20px 10%;
  transition:.25s;
  position:relative;
}
.card:hover{ box-shadow:0 0 10px rgba(26,255,135,0.14); }

/* category badge */
.cat{
  position:absolute;
  top:14px;
  right:16px;
  background:var(--accent);
  color:#000;
  padding:6px 12px;
  border-radius:999px;
  font-size:12px;
  font-weight:800;
}

/* post header */
.post-header{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
}
.post-user{
  display:flex;
  align-items:center;
  gap:8px;
}
.post-date{
  font-size:12px;
  color:var(--muted);
  margin-left:10px;
}

/* inputs */
input, textarea, select{
  width:100%;
  background:#111;
  color:var(--text);
  border:1px solid rgba(26,255,135,0.10);
  border-radius:10px;
  padding:10px 12px;
  margin:6px 0;
  outline:none;
}
textarea{ min-height:120px; resize:vertical; }

input:focus, textarea:focus, select:focus{
  border-color:var(--accent);
  box-shadow:0 0 0 3px rgba(26,255,135,0.10);
}

/* buttons */
.btn, button{
  background:var(--accent);
  color:#000;
  padding:8px 16px;
  border-radius:10px;
  border:none;
  cursor:pointer;
  font-weight:700;
  transition:.25s;
}
.btn:hover, button:hover{ background:#11cc66; }

.btn-secondary{
  background:#111;
  color:var(--accent);
  border:1px solid var(--border);
}
.btn-secondary:hover{
  background:var(--accent);
  color:#000;
  border-color:var(--accent);
}

/* comments */
.comment{
  margin-top:14px;
  padding:14px;
  border-left:4px solid var(--accent);
  background:#101010;
  border-radius:12px;
}
.reply{
  margin-left:30px;
  margin-top:8px;
  color:#ccc;
}

.hidden{ display:none; }

img{
  border-radius:12px;
  margin-top:10px;
  max-width:100%;
  height:auto;
  border:1px solid rgba(26,255,135,0.10);
}

/* edit/delete */
.btn-delete{
  background:none;
  border:none;
  color:#ff5b5b;
  cursor:pointer;
  font-size:16px;
  margin-left:10px;
}
.btn-edit{
  color:#57a0ff;
  font-size:16px;
  margin-left:6px;
  text-decoration:none;
}

/* logo */
.logo{ height:48px; }

/* logout */
.logout-btn{
  background:#ff4d4d;
  color:#000;
  padding:8px 14px;
  border-radius:10px;
  font-weight:800;
  text-decoration:none;
}
.logout-btn:hover{ opacity:.9; }

/* responsive */
@media (max-width: 900px){
  header{ padding:14px 18px; flex-wrap:wrap; gap:10px; }
  .submenu, .search-bar, .quick-links{ padding-left:6%; padding-right:6%; }
  .card{ margin-left:6%; margin-right:6%; }
}
</style>

</head>
<body>
<header>
  <div class="logo-container">
    <img src="../../public/images/logo.png" alt="Logo" class="logo">
  </div>
  <nav>
    <a href="index.php">Home</a>
    <a href="list.php">Games</a>
    <a href="tournaments.php">Tournaments</a>
    <a href="#" id="communityMenuToggle" class="active">Community</a>
    <a href="event.php">Events</a>
    <a href="feedback.php">Feedback</a>
    <a href="profile.php">My Profile</a>
    <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
  </nav>
</header>

<div id="communitySubmenu" class="submenu">
  <a href="community.php">Feed</a>
  <a href="archive.php">Archive</a>
  <a href="chat.php">Messages</a>
</div>
<section>
  <div class="quick-links">
    <span class="pill">Showing <?php echo count($posts); ?> posts<?php echo $selectedCategory ? ' in ' . htmlspecialchars($selectedCategory) : ''; ?></span>
    <a class="btn-secondary btn" href="archive.php">Archived posts</a>
    <a class="btn-secondary btn" href="chat.php">Messages</a>
  </div>

  <?php if ($flashError): ?>
    <div style="background:#331111; border:1px solid #ff4444; color:#fdd; padding:12px 15px; border-radius:8px; margin:10px 6%;">
      <?php echo htmlspecialchars($flashError); ?>
    </div>
  <?php endif; ?>

  <form method="GET" class="search-bar">
    <select name="category">
      <option value="">All</option>
      <option value="General" <?php echo $selectedCategory==='General' ? 'selected' : ''; ?>>General</option>
      <option value="Action" <?php echo $selectedCategory==='Action' ? 'selected' : ''; ?>>Action</option>
      <option value="Adventure" <?php echo $selectedCategory==='Adventure' ? 'selected' : ''; ?>>Adventure</option>
      <option value="Racing" <?php echo $selectedCategory==='Racing' ? 'selected' : ''; ?>>Racing</option>
      <option value="Strategy" <?php echo $selectedCategory==='Strategy' ? 'selected' : ''; ?>>Strategy</option>
    </select>
    <button class="btn" type="submit">Apply</button>
  </form>

  <form action="../../Controller/add_post_action.php" method="POST" enctype="multipart/form-data" class="card">
    <h3 style="color:#1aff87;margin-top:0;">Create a Post</h3>
    <textarea name="content" maxlength="1000" placeholder="Write something..." required></textarea>
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px;align-items:center;">
      <select name="category" style="background:#111;color:#1aff87;border:1px solid #1aff8720;padding:8px 10px;border-radius:10px;">
        <option value="General">General</option>
        <option value="Action">Action</option>
        <option value="Adventure">Adventure</option>
        <option value="Racing">Racing</option>
        <option value="Strategy">Strategy</option>
      </select>
      <label class="btn" style="margin:0;display:inline-flex;align-items:center;gap:8px;">
        Upload Image
        <input type="file" hidden name="image" accept="image/*">
      </label>
      <button class="btn" type="submit" style="margin:0;">Publish</button>
    </div>
  </form>

  <?php foreach ($posts as $post): ?>
  <div class="card" data-post-id="<?php echo (int) $post['id']; ?>">

    <?php if (!empty($post['category'])): ?>
      <span class="cat"><?php echo htmlspecialchars($post['category']); ?></span>
    <?php endif; ?>

    <div class="post-header">
      <div class="post-user">
        <b>@<?php echo htmlspecialchars($post['username'] ?? 'user'); ?></b>
        <span class="post-date"><?php echo date('d M Y - H:i', strtotime($post['created_at'])); ?></span>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <button class="btn-secondary" data-action="react" data-type="like" data-target="post<?php echo (int) $post['id']; ?>">❤️ <span class="like-count"><?php echo (int) ($post['reactions']['like'] ?? 0); ?></span></button>
        <button class="btn-secondary" data-action="react" data-type="dislike" data-target="post<?php echo (int) $post['id']; ?>">💔 <span class="dislike-count"><?php echo (int) ($post['reactions']['dislike'] ?? 0); ?></span></button>

        <?php if ((int) ($post['user_id'] ?? 0) === (int) $userId || $userRole === 'admin'): ?>
          <button class="btn-secondary" data-action="archive" data-post-id="<?php echo (int) $post['id']; ?>">Archive</button>
          <button class="btn-secondary" data-action="edit" data-post-id="<?php echo (int) $post['id']; ?>">Edit</button>
          <button class="btn-secondary" style="background:#ff3333;color:#fff;" data-action="delete" data-post-id="<?php echo (int) $post['id']; ?>">Delete</button>
        <?php endif; ?>
      </div>
    </div>

    <p style="white-space:pre-wrap;line-height:1.5;margin-top:12px;"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>

    <?php if (!empty($post['image'])): ?>
        <img src="../../public/images/uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="post image">
    <?php endif; ?>

    <button class="btn" style="margin-top:12px;" onclick="document.getElementById('comments-<?php echo (int) $post['id']; ?>').classList.toggle('hidden')">
      💬 See comments (<?php echo (int) ($post['comment_count'] ?? count($post['comments'] ?? [])); ?>)
    </button>

    <div id="comments-<?php echo (int) $post['id']; ?>" class="hidden" style="margin-top:12px;">
      <?php if (!empty($post['comments'])): ?>
        <?php foreach ($post['comments'] as $comment): ?>
          <div class="comment" data-comment-id="<?php echo (int) $comment['id']; ?>">
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <div>
                <b>@<?php echo htmlspecialchars($comment['username'] ?? 'user'); ?></b>
                <span class="post-date"><?php echo date('d M Y - H:i', strtotime($comment['created_at'])); ?></span>
              </div>
              <?php if ((int) ($comment['user_id'] ?? 0) === (int) $userId || $userRole === 'admin'): ?>
                <div>
                  <button class="btn-secondary" data-action="edit-comment" data-comment-id="<?php echo (int) $comment['id']; ?>">Edit</button>
                  <button class="btn-secondary" style="background:#ff3333;color:#fff;" data-action="delete-comment" data-comment-id="<?php echo (int) $comment['id']; ?>">Delete</button>
                </div>
              <?php endif; ?>
            </div>

            <div style="margin-top:8px;"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></div>

            <?php if (!empty($comment['replies'])): ?>
              <div style="margin-top:10px;padding-left:12px;border-left:2px solid #1aff8722;">
                <?php foreach ($comment['replies'] as $reply): ?>
                  <div class="reply" data-reply-id="<?php echo (int) $reply['id']; ?>">
                    <strong style="color:#1aff87;">@<?php echo htmlspecialchars($reply['username'] ?? 'user'); ?></strong>
                    <span><?php echo nl2br(htmlspecialchars($reply['content'])); ?></span>
                    <?php if ((int) ($reply['user_id'] ?? 0) === (int) $userId || $userRole === 'admin'): ?>
                      <button class="btn-secondary" style="padding:2px 6px;font-size:0.8rem;" data-action="edit-reply" data-reply-id="<?php echo (int) $reply['id']; ?>">Edit</button>
                      <button class="btn-secondary" style="padding:2px 6px;font-size:0.8rem;background:#ff3333;color:#fff;" data-action="delete-reply" data-reply-id="<?php echo (int) $reply['id']; ?>">Delete</button>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <form class="reply-form" style="margin-top:10px;display:flex;gap:8px;" data-comment-id="<?php echo (int) $comment['id']; ?>">
              <input type="text" name="content" placeholder="Write a reply..." maxlength="500" style="flex:1;padding:8px;border-radius:8px;border:1px solid #1aff8720;background:#111;color:#fff;">
              <button type="submit" class="btn">Reply</button>
            </form>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <form class="comment-form" data-post-id="<?php echo (int) $post['id']; ?>" style="margin-top:12px;display:flex;gap:10px;">
        <input type="text" name="content" placeholder="Write a comment..." maxlength="500" style="flex:1;padding:10px 12px;border-radius:10px;border:1px solid #1aff8720;background:#111;color:#fff;">
        <button type="submit" class="btn">Comment</button>
      </form>
    </div>

  </div>
  <?php endforeach; ?>
</section>

<script>
  const parseJson = (res) => res.json().catch(() => ({ success: false, message: 'Invalid response' }));
  const showError = (msg) => alert(msg || 'Action failed. Please try again.');

  document.querySelectorAll('[data-action="react"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.getAttribute('data-target');
      const type = btn.getAttribute('data-type');

      fetch('../../Controller/reaction_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `target=${encodeURIComponent(target)}&type=${encodeURIComponent(type)}`
      }).then(parseJson).then(data => {
        if (data.success) {
          const card = btn.closest('.card');
          if (card) {
            const likeEl = card.querySelector('.like-count');
            const dislikeEl = card.querySelector('.dislike-count');
            if (likeEl && data.counts.like !== undefined) likeEl.textContent = data.counts.like;
            if (dislikeEl && data.counts.dislike !== undefined) dislikeEl.textContent = data.counts.dislike;
          }
        } else {
          showError(data.message);
        }
      }).catch(() => showError());
    });
  });

  document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const postId = form.getAttribute('data-post-id');
      const content = form.querySelector('input[name="content"]').value.trim();
      if (!content) return;

      fetch('../../Controller/add_comment_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${encodeURIComponent(postId)}&content=${encodeURIComponent(content)}`
      }).then(parseJson).then(data => {
        if (data.success) {
          window.location.reload();
        } else {
          showError(data.message);
        }
      }).catch(() => showError());
    });
  });

  document.querySelectorAll('.reply-form').forEach(form => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const commentId = form.getAttribute('data-comment-id');
      const content = form.querySelector('input[name="content"]').value.trim();
      if (!content) return;

      fetch('../../Controller/add_reply_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `comment_id=${encodeURIComponent(commentId)}&content=${encodeURIComponent(content)}`
      }).then(parseJson).then(data => {
        if (data.success) {
          window.location.reload();
        } else {
          showError(data.message);
        }
      }).catch(() => showError());
    });
  });

  document.querySelectorAll('[data-action="delete"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const postId = btn.getAttribute('data-post-id');
      if (!confirm('Delete this post?')) return;
      fetch('../../Controller/delete_post_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${encodeURIComponent(postId)}`
      }).then(parseJson).then(data => {
        if (data.success) {
          window.location.reload();
        } else {
          showError(data.message || 'Unable to delete');
        }
      }).catch(() => showError());
    });
  });

  document.querySelectorAll('[data-action="archive"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const postId = btn.getAttribute('data-post-id');
      fetch('../../Controller/archive_post_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${encodeURIComponent(postId)}`
      }).then(parseJson).then(data => {
        if (data.success) {
          window.location.reload();
        } else {
          showError(data.message);
        }
      }).catch(() => showError());
    });
  });
</script>
</body>
</html>
</body>
</html>
