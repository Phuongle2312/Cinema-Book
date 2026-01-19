<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;
use App\Models\Showtime;
use App\Models\Room;
use Carbon\Carbon;

$conan = Movie::where('title', 'like', '%Conan%')->first();
$room = Room::first();

try {
    Showtime::create([
        'movie_id' => $conan->movie_id,
        'room_id' => $room->id,
        'start_time' => Carbon::parse('2026-01-20 20:00:00'),
        'end_time' => Carbon::parse('2026-01-20 22:00:00'), // Explicitly adding end_time
        'base_price' => 100000,
    ]);
    echo "Success";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage();
}
