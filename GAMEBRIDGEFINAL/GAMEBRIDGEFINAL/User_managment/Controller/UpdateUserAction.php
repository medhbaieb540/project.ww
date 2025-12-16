<?php
// Controller/UpdateUserAction.php
session_start();

require_once __DIR__ . '/UserController.php';

$userC = new UserController();
$currentRole = $_SESSION['user_role'] ?? null;

if (!isset($_GET['id'])) {
    die("ERROR: Missing ID in URL");
}

$id = intval($_GET['id']);
$user = $userC->getUserById($id);

if (!$user) {
    die("ERROR: User not found");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = $_POST['username'];
    $email      = $_POST['email'];
    $role       = $_POST['user_role'];
    $birth_date = $_POST['birthdate'];
    $address    = $_POST['address'];
    $gender     = $_POST['gender'];

    $userC->updateUser($id, $username, $email, $role, $birth_date, $address, $gender);

    if ($currentRole === 'super_admin' && !empty($_POST['password'])) {
        $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $userC->updateUserPassword($id, $hashed);
    }

    header("Location: ../View/BackOffice/admin.php?section=users");
    exit;
}
