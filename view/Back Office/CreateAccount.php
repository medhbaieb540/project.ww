<?php


include '../../controller/UserController.php';
require_once __DIR__ . '/../../Model/User.php';

$error = "";
$userC = new UserController();

if (
    isset($_POST["username"]) && 
    isset($_POST["email"]) && 
    isset($_POST["password"]) && 
    isset($_POST["confirmPassword"]) && 
    isset($_POST["accountType"])
) {
    if (
        !empty($_POST["username"]) && 
        !empty($_POST["email"]) && 
        !empty($_POST["password"]) && 
        !empty($_POST["confirmPassword"]) && 
        !empty($_POST["accountType"]) 
    ) {

        if ($_POST["password"] !== $_POST["confirmPassword"]) {
            $error = "Passwords do not match!";
            echo $error;
            exit;
        }

        $user = new User(
            null,                      // id
            $_POST['username'],
            $_POST['email'],
            $_POST['password'],
            $_POST['accountType']   
        );

        
        try {
            $userC->addUser($user);
            header("Location: ../Front Office/login.html");
            exit; 
        } catch (Exception $e) {
            echo "Controller error: " . $e->getMessage();
            exit;
        }

    } else {
        $error = "Missing information";
        echo $error;
        exit;
    }
}
?>


