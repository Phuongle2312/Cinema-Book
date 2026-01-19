<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;
use App\Models\Showtime;
use Carbon\Carbon;

$conan = Movie::where('title', 'like', '%Conan%')->first();
$room = App\Models\Room::first();

if (!$conan || !$room) {
    echo "Setup failed.\n";
    exit;
}

echo "Attempting to create showtime...\n";

try {
    $showtime = Showtime::create([
        'movie_id' => $conan->movie_id,
        'room_id' => $room->id,
        'start_time' => Carbon::now()->addDay()->setHour(20)->setMinute(0),
        'base_price' => 100000,
    ]);
    echo "SUCCESS! Created ID: {$showtime->id}\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
