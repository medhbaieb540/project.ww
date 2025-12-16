<?php
// Controller/CreateAccountAction.php

include __DIR__ . '/UserController.php';
require_once __DIR__ . '/../Model/User.php';

$error = "";
$userC = new UserController();

if (
    isset($_POST["username"]) &&
    isset($_POST["email"]) &&
    isset($_POST["password"]) &&
    isset($_POST["confirmPassword"]) &&
    isset($_POST["accountType"]) &&
    isset($_POST["gender"]) &&
    isset($_POST["birthdate"]) &&
    isset($_POST["address"])
) {
    if (
        !empty($_POST["username"]) &&
        !empty($_POST["email"]) &&
        !empty($_POST["password"]) &&
        !empty($_POST["confirmPassword"]) &&
        !empty($_POST["accountType"]) &&
        !empty($_POST["gender"]) &&
        !empty($_POST["birthdate"]) &&
        !empty($_POST["address"])
    ) {

        if ($_POST["password"] !== $_POST["confirmPassword"]) {
            $error = "Passwords do not match!";
        } else {

            $hashedPassword = password_hash($_POST["password"], PASSWORD_DEFAULT);

            $user = new User(
                null,                      // id
                $_POST['username'],
                $_POST['email'],
                $hashedPassword,
                $_POST['accountType'],     // role
                $_POST['birthdate'],
                $_POST['address'],
                $_POST['gender']
            );

            try {
               $userC->addUser($user);
                // جيب آخر id انضاف (لازم تكون عندك دالة ترجع lastInsertId)
               $newUserId = $userC->getLastInsertedId(); // أو رجعها من addUser مباشرة  

                // نخزّن اليوزر مباشرة في session
               session_start();
                $_SESSION['user_id']   = $newUserId;
                $_SESSION['username']  = $_POST['username'];
                $_SESSION['email']     = $_POST['email'];
                $_SESSION['user_role'] = $_POST['accountType'];

                if ($_POST['accountType'] === 'developer') {
                    // 👈 يروح مباشرة على create company
                    header("Location: ../View/FrontOffice/company_search.php");
                exit;
                }   else {
                  // 👈 player
                    header("Location: login.php");
                    exit;
                }

                } catch (Exception $e) {
                     $error = "Controller error: " . $e->getMessage();
                }   
        }
    } else {
        $error = "Missing information";
    }
}


