<?php

use App\Models\Showtime;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing Showtime::with(['movie', 'room.theater'])\n";

    $showtimes = Showtime::with(['movie', 'room.theater'])
        ->orderBy('show_date', 'desc')
        ->limit(5)
        ->get();

    echo "Count: " . $showtimes->count() . "\n";

    foreach ($showtimes as $showtime) {
        echo "Showtime ID: " . $showtime->showtime_id . "\n";
        echo "Movie: " . ($showtime->movie ? $showtime->movie->title : 'NULL') . "\n";
        echo "Room: " . ($showtime->room ? $showtime->room->name : 'NULL') . "\n";

        if ($showtime->room) {
            echo "Theater: " . ($showtime->room->theater ? $showtime->room->theater->name : 'NULL') . "\n";
        } else {
            echo "Theater: N/A (No Room)\n";
        }
        echo "-------------------\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
