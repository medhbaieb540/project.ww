<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controller/CompanyController.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$companyC = new CompanyController($pdo);

$error = "";
$results = [];
$q = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q = trim($_POST['company_name'] ?? '');

    if ($q === "") {
        $error = "Please enter a company name.";
    } else {
        $results = $companyC->findByName($q);
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
  <link rel="stylesheet" href="../../public/css/company_search.css" />
</head>

<body>
  <div class="bg-orbit"></div>

  <!-- ✅ GRID WRAPPER: search in the middle, results on the right -->
  <div class="page-wrap">

    <!-- ================= SEARCH CARD (MIDDLE) ================= -->
    <div class="container search-card">

      <div class="logo">
        <img class="logo-img" src="../../public/images/logo.jpg" alt="GameBridge Logo" />
        <div class="logo-text">GameBridge</div>
      </div>

      <h1>Find your company</h1>
      <p class="subtitle">Search for your studio and join it to continue as a developer.</p>

      <form method="POST" action="">
        <div class="form-group">
          <label class="field-label">Company name</label>
          <div class="input-wrap">
            <input
              type="text"
              name="company_name"
              placeholder="e.g. UnityForge Studio"
              value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>"
            />
            <span class="input-icon">🔍</span>
          </div>
        </div>

        <button type="submit" class="btn-primary">Search</button>

        <?php if (!empty($error)): ?>
          <div class="status error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="or-divider">
          <span></span><p>or</p><span></span>
        </div>

        <div class="alt-action">
          <p>Can’t find your company?</p>
          <a href="create_company.php" class="btn-outline">Create your own company</a>
        </div>
      </form>
    </div>

    <!-- ================= RESULTS (RIGHT) ================= -->
    <?php if (!empty($results)): ?>
      <div class="results-card">
        <h3 class="results-title">Companies found</h3>

        <div class="table-wrap">
          <table class="results-table">
            <thead> 
              <tr>
                <th>Name</th>
                <th>Status</th>
                <th class="col-action">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($results as $c): ?>
                <tr>
                  <td><?= htmlspecialchars($c['name']) ?></td>
                  <td><?= htmlspecialchars($c['status']) ?></td>
                  <td class="col-action">
                    <a class="btn-small" href="join_company_action.php?company_id=<?= (int)$c['id'] ?>">Join</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

  </div>
</body>
</html>
