<?php

// Query database untuk melihat semua tabel yang ada
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=kominfo_sangihe', 'root', '');
$stmt = $pdo->query('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = "kominfo_sangihe" ORDER BY TABLE_NAME');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "=== TABEL DI DATABASE ===" . PHP_EOL;
echo implode(PHP_EOL, $tables) . PHP_EOL;
echo "Total: " . count($tables) . " tabel" . PHP_EOL;
