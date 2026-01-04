<?php

echo "PHP Version: " . PHP_VERSION . PHP_EOL;
echo "Testing MySQL Connection..." . PHP_EOL;

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    echo "✓ MySQL Connection: OK" . PHP_EOL;
    echo "✓ MySQL Version: " . $pdo->query('SELECT VERSION()')->fetchColumn() . PHP_EOL;
    
    // Test database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS cinema_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database 'cinema_booking' ready" . PHP_EOL;
    
} catch (Exception $e) {
    echo "✗ MySQL Connection FAILED: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
