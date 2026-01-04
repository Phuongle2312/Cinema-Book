<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=cinema_booking', 'root', '');
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

echo "Checking for personal_access_tokens table..." . PHP_EOL;
echo in_array('personal_access_tokens', $tables) ? "✓ EXISTS" : "✗ NOT FOUND" . PHP_EOL;

echo PHP_EOL . "All tables:" . PHP_EOL;
foreach($tables as $t) {
    echo "  - $t" . PHP_EOL;
}
