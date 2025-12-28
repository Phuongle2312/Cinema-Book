<?php

echo "========================================" . PHP_EOL;
echo "  KIỂM TRA DATABASE HIỆN TẠI" . PHP_EOL;
echo "========================================" . PHP_EOL . PHP_EOL;

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=cinema_booking', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Lấy tất cả bảng
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    sort($tables);
    
    echo "Tổng số bảng: " . count($tables) . PHP_EOL . PHP_EOL;
    
    // Danh sách bảng theo yêu cầu
    $required = [
        // Core
        'users' => 'Người dùng',
        'genres' => 'Thể loại phim',
        'languages' => 'Ngôn ngữ',
        'cast' => 'Diễn viên/Đạo diễn',
        'theaters' => 'Rạp chiếu',
        'screens' => 'Phòng chiếu',
        'movies' => 'Phim',
        
        // Pivot
        'movie_genre' => 'Phim-Thể loại',
        'movie_language' => 'Phim-Ngôn ngữ',
        'movie_cast' => 'Phim-Diễn viên',
        
        // Booking System
        'showtimes' => 'Lịch chiếu',
        'seat_types' => 'Loại ghế (VIP/Thường) - YÊU CẦU MỚI',
        'seats' => 'Ghế vật lý (Sơ đồ phòng)',
        'pricing_rules' => 'Quy tắc giá động - YÊU CẦU MỚI',
        
        // Transaction
        'bookings' => 'Đơn hàng',
        'tickets' => 'Chi tiết vé - YÊU CẦU MỚI',
        'booking_seats' => 'Ghế đã đặt',
        'seat_locks' => 'Khóa ghế tạm',
        'transactions' => 'Giao dịch thanh toán',
        'reviews' => 'Đánh giá phim'
    ];
    
    echo "KIỂM TRA CÁC BẢNG:" . PHP_EOL;
    echo str_repeat("-", 60) . PHP_EOL;
    
    $missing = [];
    foreach ($required as $table => $desc) {
        $exists = in_array($table, $tables);
        if ($exists) {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo "✓ $table - $desc ($count rows)" . PHP_EOL;
        } else {
            echo "✗ $table - $desc [THIẾU]" . PHP_EOL;
            $missing[] = $table;
        }
    }
    
    echo PHP_EOL . str_repeat("=", 60) . PHP_EOL;
    echo "KẾT QUẢ:" . PHP_EOL;
    echo "  - Có: " . (count($required) - count($missing)) . "/" . count($required) . " bảng" . PHP_EOL;
    echo "  - Thiếu: " . count($missing) . " bảng" . PHP_EOL;
    
    if (count($missing) > 0) {
        echo PHP_EOL . "BẢNG CẦN TẠO:" . PHP_EOL;
        foreach ($missing as $t) {
            echo "  • $t" . PHP_EOL;
        }
    }
    
    // Liệt kê bảng thừa (không cần thiết)
    $extra = array_diff($tables, array_keys($required), [
        'migrations', 'cache', 'cache_locks', 'jobs', 'job_batches', 
        'failed_jobs', 'password_reset_tokens', 'sessions', 'personal_access_tokens'
    ]);
    
    if (count($extra) > 0) {
        echo PHP_EOL . "BẢNG THỪA (có thể xóa):" . PHP_EOL;
        foreach ($extra as $t) {
            echo "  • $t" . PHP_EOL;
        }
    }
    
} catch (Exception $e) {
    echo "LỖI: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
