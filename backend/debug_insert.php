<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Determine a valid room_id first
    $room = DB::table('rooms')->first();
    if (!$room) {
        die("No rooms found! Run CinemaSeeder (rooms part) first or check logic.\n");
    }
    
    echo "Column 'type': " . (Schema::hasColumn('seats', 'type') ? 'YES' : 'NO') . "\n";
    echo "Column 'seat_type': " . (Schema::hasColumn('seats', 'seat_type') ? 'YES' : 'NO') . "\n";

    DB::table('seats')->insert([
        'room_id' => $room->room_id,
        'row' => 'TEST',
        'number' => 999,
        'seat_code' => 'TEST999',
        'seat_type' => 'standard',
        'is_available' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "Insert SUCCESS!\n";
} catch (\Exception $e) {
    echo "F: " . substr($e->getMessage(), 0, 200) . "\n";
}
