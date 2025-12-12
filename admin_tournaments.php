<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/AdminTournamentController.php';

$controller = new AdminTournamentController($pdo);
$controller->handleRequest();
