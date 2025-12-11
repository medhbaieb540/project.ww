<?php
session_start();
require 'config.php';

/* ============== USER DEFAULT ============== */
if (!isset($_SESSION['user_id'])) {
    // Development default (remove/change in production)
    $_SESSION['user_id']  = 1;
    $_SESSION['username'] = 'AdminUser';
    $_SESSION['role']     = 'admin';
}

$username  = $_SESSION['username'];
$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$is_admin  = ($user_role === 'admin');

/* ============== SEARCH / SORT / FILTER ============== */
$search = trim($_GET['search'] ?? '');
$sort   = $_GET['sort'] ?? 'new';
$cat    = $_GET['category'] ?? '';

$order = "posts.created_at DESC";
if ($sort === "old")      $order = "posts.created_at ASC";
if ($sort === "likes")    $order = "like_count DESC";
if ($sort === "comments") $order = "comment_count DESC";

/* ============== CREATE POST ============== */
if (isset($_POST['create_post'])) {
    $text     = filterBadWords(trim($_POST['post_content'] ?? ''));
    $category = $_POST['category'] ?? 'Action';
    $imageName = '';

    if (!empty($_FILES['post_image']['name'])) {
        if (!is_dir("uploads")) mkdir("uploads", 0777, true);
        $imageName = time() . "_" . basename($_FILES["post_image"]["name"]);
        move_uploaded_file($_FILES["post_image"]["tmp_name"], "uploads/" . $imageName);
    }

    if ($text !== '') {
        $stmt = $pdo->prepare("
            INSERT INTO posts (user_id, content, image, category, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $text, $imageName, $category]);
    }

    header("Location: community.php");
    exit();
}

/* ============== ADD COMMENT ============== */
if (isset($_POST['add_comment'])) {
    $comment = filterBadWords(trim($_POST['comment_text'] ?? ''));
    $pid = (int)($_POST['post_id'] ?? 0);

    if ($pid > 0 && $comment !== '') {
        $stmt = $pdo->prepare("
            INSERT INTO comments (post_id, user_id, content, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$pid, $user_id, $comment]);
    }

    header("Location: community.php");
    exit();
}

/* ============== ADD REPLY ============== */
if (isset($_POST['add_reply'])) {
    $reply = filterBadWords(trim($_POST['reply_text'] ?? ''));
    $cid = (int)($_POST['comment_id'] ?? 0);

    if ($cid > 0 && $reply !== '') {
        $stmt = $pdo->prepare("
            INSERT INTO replies (comment_id, user_id, content, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$cid, $user_id, $reply]);
    }

    header("Location: community.php");
    exit();
}

/* ============== REACTIONS (like/dislike) ============== */
if (isset($_POST['react']) && isset($_POST['target'])) {
    $target = $_POST['target'];
    $type   = $_POST['react'];

    // toggle reaction by username
    $check = $pdo->prepare("SELECT * FROM reactions WHERE target = ? AND username = ?");
    $check->execute([$target, $username]);

    if ($check->rowCount()) {
        $pdo->prepare("DELETE FROM reactions WHERE target = ? AND username = ?")
            ->execute([$target, $username]);
    } else {
        $pdo->prepare("INSERT INTO reactions (target, username, type) VALUES (?, ?, ?)")
            ->execute([$target, $username, $type]);
    }

    header("Location: community.php");
    exit();
}

/* ============== COUNT REACTIONS HELPER ============== */
function countReact($pdo, $target, $type) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reactions WHERE target = ? AND type = ?");
    $stmt->execute([$target, $type]);
    return (int)$stmt->fetchColumn();
}

/* ============== BUILD POSTS QUERY WITH FILTERS ============== */
$where = "WHERE COALESCE(posts.is_archived,0) = 0";
$params = [];

if ($search !== '') {
    $where .= " AND (posts.content LIKE ? OR users.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($cat)) {
    $where .= " AND posts.category = ?";
    $params[] = $cat;
}

/* We'll fetch posts with comment_count and like_count (for sorting) */
$sql = "
SELECT posts.*,
       users.username,
       (SELECT COUNT(*) FROM comments WHERE post_id = posts.id) AS comment_count,
       (SELECT COUNT(*) FROM reactions WHERE target = CONCAT('post',posts.id) AND type='like') AS like_count
FROM posts
JOIN users ON posts.user_id = users.id
$where
ORDER BY $order
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ============== FETCH COMMENTS & REPLIES (global lists) ============== */
$comments = $pdo->query("
    SELECT comments.*, users.username
    FROM comments
    JOIN users ON comments.user_id = users.id
    ORDER BY comments.created_at ASC
")->fetchAll(PDO::FETCH_ASSOC);

$replies = $pdo->query("
    SELECT replies.*, users.username
    FROM replies
    JOIN users ON replies.user_id = users.id
    ORDER BY replies.created_at ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>GameBridge | Community</title>
<style>
/* Keep your project styles — unchanged */
body{background:#0c0c0c;color:white;font-family:Arial}
header{
  background:#111;padding:15px 6%;
  border-bottom:2px solid #1aff87;
  display:flex;justify-content:space-between;align-items:center
}
nav a{color:white;margin-left:16px;text-decoration:none}
nav a:hover{color:#1aff87}
.user-badge{
  border:1px solid #1aff87;
  padding:6px 14px;border-radius:20px;
  color:#1aff87;font-size:13px
}
.search-bar{padding:20px 6%;display:flex;gap:10px;justify-content:flex-end}
.search-bar input, .search-bar select{padding:10px;background:#111;color:white;border:1px solid #1aff8720;border-radius:10px}
.card{
  background:#1a1a1a;
  border:1px solid #1aff8720;
  border-radius:16px;
  padding:22px;
  margin:20px 6%;
  position:relative;
}
textarea{
  width:100%;background:#111;color:white;
  border:1px solid #333;border-radius:12px;padding:12px
}
.btn{
  background:#1aff87;color:black;
  padding:7px 16px;border-radius:10px;
  border:none;cursor:pointer;margin-top:10px
}
.cat{
  position:absolute;top:14px;right:16px;
  background:#1aff87;color:black;
  padding:5px 12px;border-radius:14px;
  font-size:12px;font-weight:bold
}
.post-header{display:flex;justify-content:space-between;align-items:center}
.post-user{display:flex;align-items:center;gap:8px}
.post-date{font-size:12px;color:#aaa;margin-left:10px}
.comment{
  margin-top:14px;padding:14px;
  border-left:4px solid #1aff87;
  background:#101010;border-radius:10px
}
.reply{
  margin-left:30px;margin-top:8px;color:#ccc
}
.hidden{display:none}
img{border-radius:14px;margin-top:10px}
.btn-delete{
  background:none;border:none;color:#ff5b5b;
  cursor:pointer;font-size:16px;margin-left:10px;
}
.btn-edit{color:#57a0ff;font-size:16px;margin-left:6px;text-decoration:none;}
</style>
</head>
<body>

<header>
  <h2>🎮 GameBridge</h2>
  <div style="display:flex;align-items:center;gap:15px;">
    <span class="user-badge">@<?= htmlspecialchars($username) ?> (<?= ucfirst($user_role) ?>)</span>
    <nav>
      <a href="community.php">Community</a>
      <a href="archive.php">Archive</a>
      <a href="inbox.php">Messages</a>
      <a href="switchuser.php">Switch User</a>
    </nav>
  </div>
</header>

<!-- SEARCH + FILTER BAR -->
<form method="GET" class="search-bar">
  <input type="text" name="search" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>">
  <select name="category">
    <option value="">All</option>
    <option value="Action" <?= ($cat==="Action")?"selected":"" ?>>Action</option>
    <option value="Adventure" <?= ($cat==="Adventure")?"selected":"" ?>>Adventure</option>
    <option value="Racing" <?= ($cat==="Racing")?"selected":"" ?>>Racing</option>
    <option value="Strategy" <?= ($cat==="Strategy")?"selected":"" ?>>Strategy</option>
  </select>
  <select name="sort">
    <option value="new" <?= ($sort==="new")?"selected":"" ?>>Newest</option>
    <option value="old" <?= ($sort==="old")?"selected":"" ?>>Oldest</option>
    <option value="likes" <?= ($sort==="likes")?"selected":"" ?>>Most liked</option>
    <option value="comments" <?= ($sort==="comments")?"selected":"" ?>>Most commented</option>
  </select>
  <button class="btn" type="submit">Apply</button>
</form>

<!-- CREATE POST -->
<form method="POST" enctype="multipart/form-data" class="card">
  <textarea name="post_content" placeholder="Write something..." required></textarea>

  <select name="category">
    <option value="Action">Action</option>
    <option value="Adventure">Adventure</option>
    <option value="Racing">Racing</option>
    <option value="Strategy">Strategy</option>
  </select>

  <label class="btn">
    Upload Image
    <input type="file" hidden name="post_image">
  </label>

  <button class="btn" name="create_post">Publish</button>
</form>

<!-- POSTS LOOP START (part 2 follows) -->
<!-- ========== POSTS LOOP ========== -->
<?php foreach ($posts as $post): ?>
<div class="card">

    <!-- CATEGORY BADGE FOR NON-ADMIN -->
    <?php if (!$is_admin): ?>
        <span class="cat"><?= htmlspecialchars($post['category']) ?></span>
    <?php endif; ?>

    <!-- ================= POST HEADER ================= -->
    <div class="post-header">

        <!-- LEFT: username + edit/delete + date -->
        <div class="post-user" style="display:flex;align-items:center;gap:8px;">
            <b>@<?= htmlspecialchars($post['username']) ?></b>

            <!-- EDIT & DELETE (post owner or admin) -->
            <?php if ($is_admin || $post['user_id'] == $user_id): ?>
                <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn-edit">✏</a>

                <form method="POST" action="delete.php" style="display:inline;">
                    <input type="hidden" name="delete_post_id" value="<?= $post['id'] ?>">
                    <button class="btn-delete">🗑</button>
                </form>
            <?php endif; ?>

            <!-- DATE + EDITED AT -->
            <span class="post-date">
                <?= date("d M Y - H:i", strtotime($post['created_at'])) ?>
                <?php if (!empty($post['is_edited'])): ?>
                    • Edited on <?= !empty($post['edited_at']) ? date("d M Y - H:i", strtotime($post['edited_at'])) : '' ?>
                <?php endif; ?>
            </span>
        </div>

        <!-- RIGHT: admin category editor -->
        <div>
            <?php if ($is_admin): ?>
                <form method="POST" action="edit_category.php" style="display:flex;align-items:center;gap:8px;">
                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                    <select name="category"
                        style="background:#111;color:#1aff87;border:1px solid #1aff8720;padding:4px 8px;border-radius:10px;">
                        <option value="Action"    <?= $post['category']=="Action"    ? "selected" : "" ?>>Action</option>
                        <option value="Adventure" <?= $post['category']=="Adventure" ? "selected" : "" ?>>Adventure</option>
                        <option value="Racing"    <?= $post['category']=="Racing"    ? "selected" : "" ?>>Racing</option>
                        <option value="Strategy"  <?= $post['category']=="Strategy"  ? "selected" : "" ?>>Strategy</option>
                    </select>
                    <button class="btn" style="padding:4px 12px;margin-top:0;">Save</button>
                </form>
            <?php endif; ?>
        </div>

    </div> <!-- END POST HEADER -->

    <!-- ================= POST CONTENT ================= -->
    <p style="white-space:pre-wrap;"><?= nl2br(htmlspecialchars($post['content'])) ?></p>

    <?php if (!empty($post['image'])): ?>
        <img src="uploads/<?= htmlspecialchars($post['image']) ?>" width="320" alt="post image">
    <?php endif; ?>

    <!-- ================= LIKE / DISLIKE ================= -->
    <form method="POST" style="margin-top:10px;">
        <input type="hidden" name="target" value="post<?= $post['id'] ?>">
        <button class="btn" name="react" value="like">❤️ <?= countReact($pdo, "post".$post['id'], "like") ?></button>
        <button class="btn" name="react" value="dislike">💔 <?= countReact($pdo, "post".$post['id'], "dislike") ?></button>
    </form>

    <!-- ================= ARCHIVE BUTTON (admin or owner only) ================= -->
    <?php if ($is_admin || $post['user_id'] == $user_id): ?>
        <form method="POST" action="archive_post.php" style="margin-top:10px;">
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <button class="btn" style="background:#ffaa00;color:black;">Archive</button>
        </form>
    <?php endif; ?>

    <!-- ================= COMMENTS TOGGLE ================= -->
    <button class="btn" style="margin-top:10px;"
        onclick="document.getElementById('comments-<?= $post['id'] ?>').classList.toggle('hidden')">
        💬 See comments (<?= (int)($post['comment_count'] ?? 0) ?>)
    </button>

    <!-- ========== COMMENTS SECTION ========== -->
    <div id="comments-<?= $post['id'] ?>" class="hidden" style="margin-top:12px;">

        <?php foreach ($comments as $c): if ($c['post_id'] == $post['id']): ?>
        <div class="comment">

            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <b>@<?= htmlspecialchars($c['username']) ?></b>
                    <span class="post-date"><?= date("d M Y - H:i", strtotime($c['created_at'])) ?></span>
                    <?php if (!empty($c['is_edited'])): ?>
                        <span class="post-date"> • Edited on <?= !empty($c['edited_at']) ? date("d M Y - H:i", strtotime($c['edited_at'])) : '' ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($is_admin || $c['user_id'] == $user_id): ?>
                    <div>
                        <a href="edit_comment.php?id=<?= $c['id'] ?>" class="btn-edit">✏</a>
                        <form method="POST" action="delete.php" style="display:inline;">
                            <input type="hidden" name="delete_comment_id" value="<?= $c['id'] ?>">
                            <button class="btn-delete">🗑</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <div style="margin-top:8px;"><?= nl2br(htmlspecialchars($c['content'])) ?></div>

            <!-- ========== REPLIES ========== -->
            <?php foreach ($replies as $r): if ($r['comment_id'] == $c['id']): ?>
                <div class="reply" style="margin-top:10px;padding:10px;border-left:3px solid #1aff87;background:#0f0f0f;border-radius:8px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            ↳ <b>@<?= htmlspecialchars($r['username']) ?></b>
                            <span class="post-date"><?= date("d M Y - H:i", strtotime($r['created_at'])) ?></span>
                            <?php if (!empty($r['is_edited'])): ?>
                                <span class="post-date"> • Edited on <?= !empty($r['edited_at']) ? date("d M Y - H:i", strtotime($r['edited_at'])) : '' ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($is_admin || $r['user_id'] == $user_id): ?>
                            <div>
                                <a href="edit_reply.php?id=<?= $r['id'] ?>" class="btn-edit">✏</a>
                                <form method="POST" action="delete.php" style="display:inline;">
                                    <input type="hidden" name="delete_reply_id" value="<?= $r['id'] ?>">
                                    <button class="btn-delete">🗑</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top:6px;"><?= nl2br(htmlspecialchars($r['content'])) ?></div>
                </div>
            <?php endif; endforeach; ?>

            <!-- ========== ADD REPLY FORM ========== -->
            <form method="POST" style="margin-top:10px;">
                <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                <textarea name="reply_text" placeholder="Write a reply..." required></textarea>
                <button class="btn" name="add_reply">Reply</button>
            </form>

        </div>
        <?php endif; endforeach; ?>

        <!-- ========== ADD COMMENT FORM ========== -->
        <form method="POST" style="margin-top:12px;">
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <textarea name="comment_text" placeholder="Write a comment..." required></textarea>
            <button class="btn" name="add_comment">Comment</button>
        </form>

    </div> <!-- END COMMENTS -->

</div> <!-- END POST CARD -->
<?php endforeach; ?>
<script>
function toggleComments(id){
    const box = document.getElementById("comments-" + id);
    if (box) box.classList.toggle("hidden");
}
</script>

</body>
</html>
