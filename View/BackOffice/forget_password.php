<?php



require_once __DIR__ . '/../../Controller/ResetPasswordController.php';

var_dump("FORGET PASSWORD PHP REACHED!");
var_dump($_POST);

$reset = new ResetPasswordController();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try{

if (isset($_POST['email']) && !empty($_POST['email'])) {

        $email = $_POST['email'];
        
        // نرسل طلب reset
        $reset->requestReset($email);
       

        // رسالة موحدة حتى لو الإيميل غير موجود (للأمان)
        $message = "If this email exists, a reset link has been sent.";
    }



    }catch(Exception $e){
            echo 'Error :'. $e->getMessage();
    }

    
     
}
?>

<!-- 
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Reset Password | GameZone</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../FrontOffice/style.css" />
  
</head>
<body>
  <div class="bg-orbit"></div>

  <div class="container">
    <div class="logo">
      <img class="logo-img" src="../FrontOffice/logo.jpg" alt="GameBridge Logo" />
      <div class="logo-text">GameBridge</div>
    </div>

    <h1>Forgot your password?</h1>
    <p class="subtitle">Enter the email address on your account and we'll send a link to reset your password.</p>

    <form id="resetForm" method = "POST" action ="forget_password.php">
      <div>
        <div class="field-label">Email address</div>
        <div class="input-wrap">
          <input type="email" id="resetEmail" name="email" placeholder="you@example.com" />
          <span class="input-icon">✉️</span>
        </div>
      </div>

      <button type="submit" class="btn-primary">Send Reset Link</button>

      <div id="resetStatus" class="status"></div>

      <p class="signup-text">
        Remembered your password?
        <a href="login.html">Back to login</a>
      </p>
    </form>
  </div>
  
 
  <script src="../FrontOffice/forget_password.js"></script>
  
</body>
</html> -->
