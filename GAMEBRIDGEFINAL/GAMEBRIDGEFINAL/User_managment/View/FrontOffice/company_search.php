<?php
session_start();
require_once __DIR__ . '/../../controller/CompanyController.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$companyC = new CompanyController();

$error = "";
$results = [];
$q = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q = trim($_POST['company_name'] ?? '');

    if ($q === "") {
        $error = "Please enter a company name.";
    } else {
        $results = $companyC->findByName($q); // ترجع array
        if (empty($results)) {
            $error = "Company not found.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Find your Company | GameBridge</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
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

    <h1>Find your company</h1>
    <p class="subtitle">
      Search for your studio and join it to continue as a developer.
    </p>

    <!-- ===== SEARCH FORM ===== -->
    <form method="POST" action="">

      <div class="form-group">
        <label class="field-label">Company name</label>
        <div class="input-wrap">
          <input
            type="text"
            name="company_name"
            placeholder="e.g. UnityForge Studio"
            value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>"
          >
          <span class="input-icon">🔍</span>
        </div>
      </div>

      <button type="submit" class="btn-primary" style="margin-top:15px;">
        Search
      </button>

      <!-- OR divider -->
      <div class="or-divider" style="margin:30px 0;">
        <span></span>
        <p>or</p>
        <span></span>
      </div>

      <!-- Create company -->
      <div style="text-align:center;">
        <p style="color:#aaa;">Can’t find your company?</p>
        <a href="create_company.php" class="btn-secondary">
          Create your own company
        </a>
      </div>

    </form>

    <!-- ===== ERROR MESSAGE ===== -->
    <?php if (!empty($error)): ?>
      <div class="status error" style="margin-top:20px;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- ===== SEARCH RESULTS ===== -->
    <?php if (!empty($results)): ?>
      <div style="margin-top:30px;">

        <h3 style="color:#1aff87; margin-bottom:15px;">
          Companies found
        </h3>

        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr style="background:#111;">
              <th style="padding:12px; text-align:left;">Name</th>
              <th style="padding:12px; text-align:left;">Status</th>
              <th style="padding:12px; text-align:center;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results as $c): ?>
              <tr style="background:#0f0f0f; border-bottom:1px solid #1aff8722;">
                <td style="padding:12px;">
                  <?= htmlspecialchars($c['name']) ?>
                </td>
                <td style="padding:12px;">
                  <?= htmlspecialchars($c['status']) ?>
                </td>
                <td style="padding:12px; text-align:center;">
                 <a href="join_company_action.php?company_id=<?= (int)$c['id'] ?>">
                  <button class="btn-primary" type="button">Join</button>
                  </a>

                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

      </div>
    <?php endif; ?>

  </div>
</body>
</html>
