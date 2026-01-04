<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=cinema_booking', 'root', '');
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Database: cinema_booking" . PHP_EOL;
    echo "Total Tables: " . count($tables) . PHP_EOL . PHP_EOL;
    
    if (count($tables) > 0) {
        foreach($tables as $table) {
            echo "  ✓ " . $table . PHP_EOL;
        }
    } else {
        echo "  (No tables found)" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
