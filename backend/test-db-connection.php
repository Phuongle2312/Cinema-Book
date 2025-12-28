<?php

echo "Testing MySQL Connection...\n\n";

try {
    $host = '127.0.0.1';
    $port = '3306';
    $dbname = 'cinema_booking';
    $username = 'root';
    $password = '';
    
    echo "Connecting to: {$host}:{$port}\n";
    echo "Database: {$dbname}\n";
    echo "Username: {$username}\n\n";
    
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connection successful!\n\n";
    
    // Test query
    $stmt = $pdo->query("SELECT DATABASE() as current_db, VERSION() as version");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Current Database: " . $result['current_db'] . "\n";
    echo "MySQL Version: " . $result['version'] . "\n\n";
    
    // Show tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables in database (" . count($tables) . "):\n";
    foreach ($tables as $table) {
        echo "  - {$table}\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
}
