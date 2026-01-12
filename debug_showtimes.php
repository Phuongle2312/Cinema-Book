<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use App\Models\Showtime;

$app->make(Kernel::class)->bootstrap();

$showtimes = Showtime::with(['room.theater.city', 'movie'])->get();

echo "Total showtimes: " . $showtimes->count() . PHP_EOL;

$cities = [];
foreach ($showtimes as $st) {
    if ($st->room && $st->room->theater && $st->room->theater->city) {
        $cityName = $st->room->theater->city->name;
        if (!isset($cities[$cityName])) {
            $cities[$cityName] = 0;
        }
        $cities[$cityName]++;
    } else {
        echo "Missing relationship for Showtime ID: " . $st->showtime_id . PHP_EOL;
    }
}

echo "Showtimes per city:" . PHP_EOL;
foreach ($cities as $name => $count) {
    echo "- " . $name . ": " . $count . PHP_EOL;
}
