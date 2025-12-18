<?php
session_start();

// امسح كل بيانات الجلسة
$_SESSION = [];

// لو في cookies للجلسة امسحها كمان
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// رجّعه على login (FrontOffice)
header("Location: login.php");

exit;
