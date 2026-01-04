<?php

echo "Tạo 6 bảng còn thiếu..." . PHP_EOL . PHP_EOL;

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=cinema_booking', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = [
        'seat_types' => "
            CREATE TABLE IF NOT EXISTS seat_types (
                seat_type_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(255) NOT NULL UNIQUE,
                base_extra_price DECIMAL(10,0) DEFAULT 0,
                description TEXT NULL,
                color_code VARCHAR(7) NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'pricing_rules' => "
            CREATE TABLE IF NOT EXISTS pricing_rules (
                pricing_rule_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                rule_type ENUM('time_based', 'day_based', 'seat_based', 'movie_based') DEFAULT 'time_based',
                conditions JSON NOT NULL,
                adjustment_type ENUM('fixed', 'percentage') DEFAULT 'fixed',
                adjustment_value DECIMAL(10,2) NOT NULL,
                priority INT DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                valid_from DATE NULL,
                valid_to DATE NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'seat_locks' => "
            CREATE TABLE IF NOT EXISTS seat_locks (
                lock_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                seat_id BIGINT UNSIGNED NOT NULL,
                showtime_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                locked_at TIMESTAMP NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                UNIQUE KEY unique_lock (showtime_id, seat_id),
                FOREIGN KEY (seat_id) REFERENCES seats(seat_id) ON DELETE CASCADE,
                FOREIGN KEY (showtime_id) REFERENCES showtimes(showtime_id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'tickets' => "
            CREATE TABLE IF NOT EXISTS tickets (
                ticket_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                booking_id BIGINT UNSIGNED NOT NULL,
                seat_id BIGINT UNSIGNED NOT NULL,
                showtime_id BIGINT UNSIGNED NOT NULL,
                ticket_code VARCHAR(30) NOT NULL UNIQUE,
                base_price DECIMAL(10,0) NOT NULL,
                seat_extra_price DECIMAL(10,0) DEFAULT 0,
                dynamic_price_adjustment DECIMAL(10,0) DEFAULT 0,
                final_price DECIMAL(10,0) NOT NULL,
                applied_pricing_rules JSON NULL,
                status ENUM('valid', 'used', 'cancelled', 'expired') DEFAULT 'valid',
                used_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                UNIQUE KEY unique_ticket_seat (showtime_id, seat_id),
                FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
                FOREIGN KEY (seat_id) REFERENCES seats(seat_id) ON DELETE CASCADE,
                FOREIGN KEY (showtime_id) REFERENCES showtimes(showtime_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'transactions' => "
            CREATE TABLE IF NOT EXISTS transactions (
                transaction_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                booking_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                transaction_code VARCHAR(30) NOT NULL UNIQUE,
                amount DECIMAL(10,0) NOT NULL,
                payment_method ENUM('cash', 'credit_card', 'momo', 'zalopay', 'vnpay') DEFAULT 'cash',
                status ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
                payment_details TEXT NULL,
                paid_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'reviews' => "
            CREATE TABLE IF NOT EXISTS reviews (
                review_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                movie_id BIGINT UNSIGNED NOT NULL,
                rating INT NOT NULL,
                comment TEXT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                UNIQUE KEY unique_user_movie (user_id, movie_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        "
    ];
    
    $success = 0;
    $failed = 0;
    
    foreach ($tables as $name => $sql) {
        try {
            $pdo->exec($sql);
            echo "✓ Tạo bảng: $name" . PHP_EOL;
            $success++;
        } catch (PDOException $e) {
            echo "✗ Lỗi bảng $name: " . $e->getMessage() . PHP_EOL;
            $failed++;
        }
    }
    
    echo PHP_EOL . "========================================" . PHP_EOL;
    echo "KẾT QUẢ: $success thành công, $failed thất bại" . PHP_EOL;
    
    if ($success > 0) {
        echo PHP_EOL . "Đang cập nhật bảng migrations..." . PHP_EOL;
        // Thêm vào bảng migrations để Laravel biết đã chạy
        $pdo->exec("INSERT INTO migrations (migration, batch) VALUES ('2025_12_22_023118_add_missing_tables', 2)");
        echo "✓ Đã cập nhật migrations table" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "LỖI: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
