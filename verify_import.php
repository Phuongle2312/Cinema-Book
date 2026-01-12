<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking Imported Data ===\n\n";

$tables = [
    'users' => 'Users',
    'movies' => 'Movies',
    'genres' => 'Genres',
    'cities' => 'Cities',
    'theaters' => 'Theaters',
    'screens' => 'Screens/Rooms',
    'seats' => 'Seats',
    'showtimes' => 'Showtimes',
    'bookings' => 'Bookings'
];

foreach ($tables as $table => $label) {
    try {
        $count = DB::table($table)->count();
        echo sprintf("%-20s: %d rows\n", $label, $count);
    } catch (Exception $e) {
        echo sprintf("%-20s: ERROR - %s\n", $label, $e->getMessage());
    }
}

echo "\n=== Checking VIP Seat Pricing ===\n";
try {
    $vipSeats = DB::table('seats')->where('seat_type', 'vip')->limit(5)->get(['seat_id', 'row', 'number', 'seat_type', 'extra_price']);
    foreach ($vipSeats as $seat) {
        echo "Seat {$seat->row}{$seat->number} (VIP): extra_price = {$seat->extra_price}\n";
    }

    $needsFix = DB::table('seats')->where('seat_type', 'vip')->where('extra_price', 0)->count();
    if ($needsFix > 0) {
        echo "\n⚠️  Warning: $needsFix VIP seats have extra_price = 0. Need to fix!\n";
    } else {
        echo "\n✅ VIP seats pricing looks good!\n";
    }
} catch (Exception $e) {
    echo "ERROR checking VIP seats: " . $e->getMessage() . "\n";
}
