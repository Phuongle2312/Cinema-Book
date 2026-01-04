<?php

echo "========================================" . PHP_EOL;
echo "  TẠO DỮ LIỆU MẪU CHO CÁC BẢNG MỚI" . PHP_EOL;
echo "========================================" . PHP_EOL . PHP_EOL;

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=cinema_booking', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. SEAT TYPES - Loại ghế
    echo "[1/2] Tạo dữ liệu Seat Types..." . PHP_EOL;
    $seatTypes = [
        ['Standard', 'standard', 0, 'Ghế thường', '#808080'],
        ['VIP', 'vip', 20000, 'Ghế VIP cao cấp', '#FFD700'],
        ['Couple', 'couple', 30000, 'Ghế đôi cho cặp đôi', '#FF69B4'],
        ['Premium', 'premium', 40000, 'Ghế Premium sang trọng', '#8B4513']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO seat_types (name, code, base_extra_price, description, color_code, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");
    
    foreach ($seatTypes as $type) {
        $stmt->execute($type);
    }
    echo "  ✓ Đã tạo " . count($seatTypes) . " loại ghế" . PHP_EOL;
    
    // 2. PRICING RULES - Quy tắc giá
    echo PHP_EOL . "[2/2] Tạo dữ liệu Pricing Rules..." . PHP_EOL;
    $pricingRules = [
        [
            'Giá Cuối Tuần',
            'day_based',
            json_encode(['days' => ['saturday', 'sunday']]),
            'fixed',
            20000,
            10,
            1
        ],
        [
            'Giá Giờ Vàng (18h-22h)',
            'time_based',
            json_encode(['start_time' => '18:00', 'end_time' => '22:00']),
            'fixed',
            15000,
            5,
            1
        ],
        [
            'Phụ Thu Ghế VIP',
            'seat_based',
            json_encode(['seat_type_codes' => ['vip']]),
            'percentage',
            10.0,
            3,
            1
        ],
        [
            'Giá Lễ Tết',
            'day_based',
            json_encode(['special_dates' => ['2025-01-01', '2025-02-10']]),
            'fixed',
            30000,
            15,
            0
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO pricing_rules (name, rule_type, conditions, adjustment_type, adjustment_value, priority, is_active, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    foreach ($pricingRules as $rule) {
        $stmt->execute($rule);
    }
    echo "  ✓ Đã tạo " . count($pricingRules) . " quy tắc giá" . PHP_EOL;
    
    echo PHP_EOL . "========================================" . PHP_EOL;
    echo "  HOÀN THÀNH!" . PHP_EOL;
    echo "========================================" . PHP_EOL;
    echo PHP_EOL;
    echo "Dữ liệu mẫu đã được tạo:" . PHP_EOL;
    echo "  • " . count($seatTypes) . " loại ghế (Standard, VIP, Couple, Premium)" . PHP_EOL;
    echo "  • " . count($pricingRules) . " quy tắc giá động" . PHP_EOL;
    echo PHP_EOL;
    echo "Bạn có thể xem trong phpMyAdmin: http://localhost/phpmyadmin" . PHP_EOL;
    
} catch (Exception $e) {
    echo "LỖI: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
