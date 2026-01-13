<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;
use App\Models\Showtime;
use App\Models\Seat;

echo "Movies: " . Movie::count() . PHP_EOL;
echo "Showtimes: " . Showtime::count() . PHP_EOL;
echo "Seats: " . Seat::count() . PHP_EOL;
