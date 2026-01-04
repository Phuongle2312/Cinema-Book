<?php

echo "========================================" . PHP_EOL;
echo "  CINEMA BOOKING - DATABASE STATUS" . PHP_EOL;
echo "========================================" . PHP_EOL . PHP_EOL;

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=cinema_booking', 'root', '');
    
    // Lấy danh sách bảng
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    sort($tables);
    
    echo "Total Tables: " . count($tables) . PHP_EOL . PHP_EOL;
    
    // Bảng cần có
    $requiredTables = [
        'users', 'genres', 'languages', 'cast',
        'theaters', 'rooms', 'movies',
        'movie_genre', 'movie_language', 'movie_cast',
        'showtimes', 'seats',
        'bookings', 'booking_details', 'booking_seats', 'seat_locks',
        'transactions', 'reviews'
    ];
    
    echo "Required Tables Status:" . PHP_EOL;
    foreach ($requiredTables as $table) {
        $exists = in_array($table, $tables);
        $status = $exists ? "✓" : "✗";
        $color = $exists ? "" : " [MISSING]";
        echo "  $status $table$color" . PHP_EOL;
    }
    
    echo PHP_EOL . "All Tables in Database:" . PHP_EOL;
    foreach($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "  • $table ($count rows)" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
