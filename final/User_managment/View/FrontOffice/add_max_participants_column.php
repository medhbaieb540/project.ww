<?php
// This script adds the max_participent column to the events table if it doesn't exist
$pdo = require __DIR__ . '/../../config/db.php';

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM events LIKE 'max_participent'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        $pdo->exec("ALTER TABLE events ADD COLUMN max_participent INT DEFAULT NULL");
        echo "✓ Column 'max_participent' added successfully to events table.";
    } else {
        echo "✓ Column 'max_participent' already exists.";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}
?>
