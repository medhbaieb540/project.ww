<?php
session_start();

require_once __DIR__ . '/../../controller/CompanyController.php';
require_once __DIR__ . '/../../Model/Company.php';

// لازم يكون مسجل دخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$ownerId = (int) $_SESSION['user_id'];

$companyC = new CompanyController();
$error   = "";
$success = "";

// معالجة الفورم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $status      = $_POST['status'] ?? 'active';

    if ($name === "" || $description === "") {
        $error = "Please fill in all required fields.";
    } else {
        // ننشئ object من Company
        $company = new Company(
            null,             // id (Auto Increment)
            $ownerId,         // owner_id from session
            $name,
            $description,
            $status,
            $address
        );

        try {
            $companyC->addCompany($company);
            $success = "Company created successfully!";

            // ممكن تعمله redirect لصفحة البروفايل أو داشبورد المطوّر
            header("Location: profile.php");
            // exit;

        } catch (Exception $e) {
            $error = "Error while creating company: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Create Company | GameBridge</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <!-- نستخدم نفس ملف CSS تبع إنشاء الحساب عشان يطلع نفس الديزاين -->
  <link rel="stylesheet" href="../../public/css/create_account.css" />
</head>

<body>
  <div class="bg-orbit"></div>

  <div class="container">
    <!-- Logo -->
    <div class="logo">
      <img class="logo-img" src="../../public/images/logo.jpg" alt="GameBridge Logo" />
      <div class="logo-text">GameBridge</div>
    </div>

    <h1>Create your company</h1>
    <p class="subtitle">
      Set up your game studio to publish games, manage tournaments and collaborate with your team.
    </p>

    <!-- رسائل الخطأ / النجاح -->
    <?php if (!empty($error)): ?>
      <div class="status error" style="margin-bottom:15px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="status success" style="margin-bottom:15px;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form id="companyForm" method="POST" action="">

      <div class="form-two-columns">

        <!-- LEFT COLUMN -->
        <div class="form-column">

          <!-- Company Name -->
          <div class="form-group">
            <label class="field-label">Company name</label>
            <div class="input-wrap">
              <input
                type="text"
                name="name"
                placeholder="UnityForge Studio"
                value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
              >
            </div>
          </div>
              <!-- Company Name -->
          <div class="form-group">
            <label class="field-label">Address</label>
            <div class="input-wrap">
              <input
                type="text"
                name="address"
                placeholder="Company address"
                value="<?= htmlspecialchars($_POST['address'] ?? '') ?>"
              >
            </div>
          </div>

          <!-- Status -->
          <div class="form-group">
            <label class="field-label">Status</label>
            <div class="input-wrap">
              <select name="status">
                <option value="active"   <?= (($_POST['status'] ?? '') === 'active')   ? 'selected' : '' ?>>Active</option>
                <option value="hidden"   <?= (($_POST['status'] ?? '') === 'hidden')   ? 'selected' : '' ?>>Hidden</option>
                <option value="suspended"<?= (($_POST['status'] ?? '') === 'suspended')? 'selected' : '' ?>>Suspended</option>
              </select>
            </div>
          </div>

        </div>

        

        <!-- RIGHT COLUMN -->
        <div class="form-column">

          <!-- Description -->
          <div class="form-group">
            <label class="field-label">Company description</label>
            <div class="input-wrap">
              <textarea
                name="description"
                rows="5"
                placeholder="Describe your studio, type of games you build, and your vision..."
                style="resize: vertical; min-height:120px; background:#050914; color:#fff; border:none; width:100%; padding:12px; border-radius:8px;"
              ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
          </div>

        </div>

      </div>

      <button type="submit" class="btn-primary" style="margin-top:25px;">
        Create Company
      </button>

      <p class="signup-text">
        Remembered your password?
        <a href="login.php">Back to login</a>
      </p>  

    </form> 

  </div>

</body>
</html>
