<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=cinema_booking', 'root', '');

echo "Cấu trúc bảng SEATS:" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

$columns = $pdo->query("DESCRIBE seats")->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo sprintf("%-20s %-15s %s %s %s" . PHP_EOL,
        $col['Field'],
        $col['Type'],
        $col['Null'] == 'YES' ? 'NULL' : 'NOT NULL',
        $col['Key'] ? "KEY:{$col['Key']}" : '',
        $col['Extra']
    );
}
