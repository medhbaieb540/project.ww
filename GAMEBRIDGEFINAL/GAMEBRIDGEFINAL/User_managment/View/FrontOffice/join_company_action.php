<?php
session_start();
require_once __DIR__ . '/../../controller/CompanyController.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$companyId = (int)($_GET['company_id'] ?? 0);

if ($companyId <= 0) {
    header("Location: company_search.php?error=" . urlencode("Invalid company."));
    exit;
}

$companyC = new CompanyController();

try {
    // (اختياري) تمنع التكرار داخل الدالة joinCompany
    $companyC->joinCompany($companyId, $userId);

    // بعد الانضمام روح على صفحة المطوّر/البروفايل
    header("Location: profile.php?joined=1");
    exit;

} catch (Exception $e) {
    header("Location: company_search.php?error=" . urlencode($e->getMessage()));
    exit;
}
