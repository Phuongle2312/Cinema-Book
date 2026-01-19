<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;

$userId = 4; // The OTHER Test User
echo "--- CHECKING BOOKINGS FOR USER ID $userId ---\n";

$bookings = Booking::where('user_id', $userId)->get();

if ($bookings->isEmpty()) {
    echo "User ID $userId has NO bookings.\n";
} else {
    foreach ($bookings as $b) {
        echo "BID: " . $b->booking_id . " | Status: " . $b->status . "\n";
    }
}
