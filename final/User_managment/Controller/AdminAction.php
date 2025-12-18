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
