<?php

use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\Theater;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'Movies Count: '.Movie::count()."\n";
echo 'Theaters Count: '.Theater::count()."\n";
echo 'Rooms Count: '.Room::count()."\n";
echo 'Showtimes Count: '.Showtime::count()."\n";

$st = Showtime::first();
if ($st) {
    echo 'First Showtime: '.json_encode($st->toArray())."\n";
} else {
    echo "No Showtimes found.\n";
}
