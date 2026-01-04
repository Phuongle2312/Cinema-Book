<?php

echo "Creating all tables..." . PHP_EOL;

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=cinema_booking', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents('create-tables.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success = 0;
    $failed = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0 || strpos($statement, 'USE ') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $success++;
            // Extract table name
            if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                echo "✓ Created table: {$matches[1]}" . PHP_EOL;
            }
        } catch (PDOException $e) {
            $failed++;
            if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                echo "✗ Failed: {$matches[1]} - " . $e->getMessage() . PHP_EOL;
            } else {
                echo "✗ Error: " . $e->getMessage() . PHP_EOL;
            }
        }
    }
    
    echo PHP_EOL . "Summary: $success created, $failed failed" . PHP_EOL;
    
} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
