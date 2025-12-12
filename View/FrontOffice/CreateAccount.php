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
            echo $error;
            exit;
        }

        $hashedPassword = password_hash($_POST["password"], PASSWORD_DEFAULT);

        // لو حابب تستخدمهم لاحقًا في الداتابيس
      

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
        $newUserId = $userC->addUser($user);

    // لو أكاونت نوعه developer -> نوديه يعمل شركة
         if ($_POST['accountType'] === 'developer') {
        $email = urlencode($_POST['email']);
        header("Location: createOrSearchCompany.php");
        exit;
        }

    // غير هيك (player مثلاً) نرجعه ع صفحة اللوجين
    header("Location: ../FrontOffice/login.php");
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


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Create Account | GameBridge</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../../public/css/create_account.css" />
</head>

<body>
  <div class="bg-orbit"></div>

  <div class="container" >
    <div class="logo">
      <img class="logo-img" src="../../public/images/logo.jpg" alt="GameBridge Logo" />
      <div class="logo-text">GameBridge</div>
    </div>

    <h1>Create your account</h1>
    <p class="subtitle">Join GameBridge to post reviews, follow discussions and get personalised recommendations.</p>

   <form id="signupForm" method="POST" action="" novalidate>

  <div class="form-two-columns">

    <!-- LEFT COLUMN -->
    <div class="form-column">

      <!-- Username -->
      <div class="form-group">
        <label class="field-label">Username</label>
        <div class="input-wrap">
          <input type="text" id="signupUsername" name="username" placeholder="gamer123">
          <span class="input-icon">👤</span>
        </div>
        <div id="UsernameStatus" class="status"></div>
      </div>

      <!-- Email -->
      <div class="form-group">
        <label class="field-label">Email</label>
        <div class="input-wrap">
          <input type="email" id="signupEmail" name="email" placeholder="you@example.com">
          <span class="input-icon">✉️</span>
        </div>
        <div id="EmailStatus" class="status"></div>
      </div>

      <!-- Account Type -->
      <div class="form-group">
        <label class="field-label">Account type</label>
        <div class="input-wrap">
          <select name="accountType" id="accountType">
            <option value="" disabled selected hidden>Select account type</option>
            <option value="player">🎮 Player</option>
            <option value="developer">💻 Developer</option>
          </select>
        </div>
        <div id="AccountTypeStatus" class="status"></div>
      </div>

      <!-- Birth date -->
      <div class="form-group">
        <label class="field-label">Birth date</label>
        <div class="input-wrap">
          <input type="date" id="birthdate" name="birthdate">
        </div>
        <div id="BirthStatus" class="status"></div>
      </div>

    </div>

    <!-- RIGHT COLUMN -->
    <div class="form-column">

      <!-- Password -->
      <div class="form-group">
        <label class="field-label">Password</label>
        <div class="input-wrap">
          <input type="password" id="signupPassword" name="password" placeholder="Create a strong password">
          <span class="input-icon">🔒</span>
        </div>
        <div id="PasswordStatus" class="status"></div>
      </div>

      <!-- Confirm Password -->
      <div class="form-group">
        <label class="field-label">Confirm password</label>
        <div class="input-wrap">
          <input type="password" id="signupConfirm" name="confirmPassword" placeholder="Repeat password">
          <span class="input-icon">🔒</span>
        </div>
        <div id="ConfirmStatus" class="status"></div>
      </div>

      <!-- Gender -->
      <div class="form-group">
        <label class="field-label">Gender</label>
        <div class="input-wrap">
          <select id="gender" name="gender">
            <option value="" disabled selected hidden>Select gender</option>
            <option value="male">♂ Male</option>
            <option value="female">♀ Female</option>
          </select>
        </div>
        <div id="GenderStatus" class="status"></div>
      </div>

      <!-- Address -->
      <div class="form-group">
        <label class="field-label">Address</label>
        <div class="input-wrap">
          <input type="text" id="address" name="address" placeholder="Your home address">
        </div>
        <div id="AddressStatus" class="status"></div>
      </div>

    </div>

  </div>

  <!-- Terms -->
  <label class="remember">
    <input type="checkbox" id="terms">
    I agree to the Terms & Privacy Policy
  </label>

  <div id="signupStatus" class="status"></div>

  <button type="submit" class="btn-primary">Create account</button>

</form>


  </div>
<script src="../../public/js/create_account.js"></script>
</body>
</html>




