<?php
// Controller/AdminAction.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../View/FrontOffice/login.php");
    exit;
}

if (!in_array($_SESSION['user_role'] ?? '', ['admin', 'super_admin'])) {
    header("Location: ../View/FrontOffice/login.php");
    exit;
}



require_once __DIR__ . '/../config/db.php';      // ✅ gives $pdo
require_once __DIR__ . '/UserController.php';   // ✅ controller

$userC = new UserController($pdo);               // ✅ CORRECT

$users  = $userC->listUsers();
$activeSection = $_GET['section'] ?? 'dashboard';

// بحث
$search = trim($_GET['search'] ?? '');

// فلترة
$filterRole   = $_GET['role']   ?? '';  // player / developer / admin ...
$filterStatus = $_GET['status'] ?? '';  // active / banned

// الترتيب
$sortField = $_GET['sort'] ?? null;     // username / email / birth_date / user_role
$sortDir   = $_GET['dir']  ?? 'ASC';    // ASC أو DESC

$users = $userC->listUsersAdvanced(
    $search,
    $filterRole,
    $filterStatus,
    $sortField,
    $sortDir
);

$previewUsers = array_slice($users, 0, 3);

// ===== FETCH DASHBOARD STATS =====
// Get user count
$userStmt = $pdo->prepare("SELECT COUNT(*) as count FROM users");
$userStmt->execute();
$userCount = $userStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Get games count
$gameStmt = $pdo->prepare("SELECT COUNT(*) as count FROM games WHERE deleted_at IS NULL OR deleted_at = ''");
$gameStmt->execute();
$gameCount = $gameStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Get tournaments count
$tourStmt = $pdo->prepare("SELECT COUNT(*) as count FROM tournaments");
$tourStmt->execute();
$liveToursCount = $tourStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Get feedback count (pending)
$feedStmt = $pdo->prepare("SELECT COUNT(*) as count FROM feedback");
$feedStmt->execute();
$pendingFeedbackCount = $feedStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Get preview games (latest 3)
$previewGamesStmt = $pdo->prepare("SELECT g.*, u.username as developer_name FROM games g 
                                   LEFT JOIN users u ON g.developer_id = u.id 
                                   WHERE g.deleted_at IS NULL OR g.deleted_at = '' 
                                   ORDER BY g.created_at DESC LIMIT 3");
$previewGamesStmt->execute();
$previewGames = $previewGamesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Get preview tournaments (latest 3)
$tournamentsStmt = $pdo->prepare("SELECT * FROM tournaments ORDER BY id DESC LIMIT 3");
$tournamentsStmt->execute();
$previewTournaments = $tournamentsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Get preview events (latest 3)
$eventsStmt = $pdo->prepare("SELECT * FROM events ORDER BY id DESC LIMIT 3");
$eventsStmt->execute();
$previewEvents = $eventsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Get preview feedback (latest 3)
$feedbackStmt = $pdo->prepare("SELECT f.*, u.username FROM feedback f 
                               LEFT JOIN users u ON f.user_id = u.id 
                               ORDER BY f.id DESC LIMIT 3");
$feedbackStmt->execute();
$previewFeedback = $feedbackStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

function buildSortLink($column, $label)
{
    $currentSort  = $_GET['sort']   ?? '';
    $currentDir   = $_GET['dir']    ?? 'ASC';
    $search       = $_GET['search'] ?? '';
    $role         = $_GET['role']   ?? '';
    $status       = $_GET['status'] ?? '';

    $newDir = 'ASC';
    if ($currentSort === $column && $currentDir === 'ASC') {
        $newDir = 'DESC';
    }

    // IMPORTANT: admin.php is now in BackOffice
    $url = "admin.php?section=users&sort=$column&dir=$newDir";

    if ($search !== '') {
        $url .= "&search=" . urlencode($search);
    }
    if ($role !== '') {
        $url .= "&role=" . urlencode($role);
    }
    if ($status !== '') {
        $url .= "&status=" . urlencode($status);
    }

    return "<a href=\"$url\" style=\"color:#1aff87; text-decoration:none;\">$label</a>";
}
