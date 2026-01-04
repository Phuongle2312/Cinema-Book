<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=cinema_booking', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Tạo bảng seat_locks (fixed)..." . PHP_EOL;

try {
    // Drop nếu tồn tại
    $pdo->exec("DROP TABLE IF EXISTS seat_locks");
    
    $sql = "
        CREATE TABLE seat_locks (
            lock_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            seat_id BIGINT UNSIGNED NOT NULL,
            showtime_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            locked_at DATETIME NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            UNIQUE KEY unique_lock (showtime_id, seat_id),
            FOREIGN KEY (seat_id) REFERENCES seats(seat_id) ON DELETE CASCADE,
            FOREIGN KEY (showtime_id) REFERENCES showtimes(showtime_id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql);
    echo "✓ Tạo bảng seat_locks thành công!" . PHP_EOL;
    
} catch (PDOException $e) {
    echo "✗ Lỗi: " . $e->getMessage() . PHP_EOL;
}
