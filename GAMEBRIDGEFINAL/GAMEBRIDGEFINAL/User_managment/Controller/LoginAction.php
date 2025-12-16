<?php
// Controller/LoginAction.php
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/UserController.php';

$userC = new UserController();
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['email'], $_POST['password'])) {
        $error = "Missing email or password";
    } else {

        $email    = trim($_POST['email']);
        $password = (string)$_POST['password'];

        if ($email === "" || $password === "") {
            $error = "Email or password cannot be empty";
        } else {
            try {
                $user = $userC->getUserByEmail($email);

                if (!$user) {
                    $error = "Email not found";
                } elseif (!password_verify($password, $user['password'])) {
                    $error = "Wrong password";
                } else {

                    // ✅ set session once
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['username']  = $user['username'];
                    $_SESSION['email']     = $user['email'];
                    $_SESSION['user_role'] = $user['user_role'];
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['birth_date']  = $user['birthdate'];
                    $_SESSION['address']     = $user['address'];
                    $_SESSION['gender'] = $user['gender'];

                    // ✅ stable redirects
                    if ($user['user_role'] === 'player' || $user['user_role'] === 'developer') {
                        header("Location:   ../View/FrontOffice/profile.php");
                        exit;
                    } else {
                        header("Location:  ../View/BackOffice/admin.php");
                        exit;
                    }
                }

            } catch (Exception $e) {
                $error = "DB error: " . $e->getMessage();
            }
        }
    }

    // If there is an error, go back to login with message (optional)
    if ($error !== "") {
        header("Location: " . BASE_URL . "/View/FrontOffice/login.php?error=" . urlencode($error));
        exit;
    }
}
