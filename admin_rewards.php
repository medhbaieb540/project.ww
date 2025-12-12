<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/AdminRewardController.php';

$controller = new AdminRewardController($pdo);
$controller->handleRequest();
