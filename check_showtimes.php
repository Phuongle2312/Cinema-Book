<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use App\Models\Showtime;
use App\Models\City;
use App\Models\Movie;

$app->make(Kernel::class)->bootstrap();

$out = "";
$out .= "--- Cities ---" . PHP_EOL;
foreach (City::all() as $city) {
    $out .= "ID: {$city->city_id} | Name: '{$city->name}' | Slug: {$city->slug}" . PHP_EOL;
}

$out .= PHP_EOL . "--- Movies (Featured) ---" . PHP_EOL;
foreach (Movie::where('is_featured', true)->get() as $movie) {
    $out .= "ID: {$movie->movie_id} | Title: '{$movie->title}'" . PHP_EOL;
}

$out .= PHP_EOL . "--- Showtimes (Total: " . Showtime::count() . ") ---" . PHP_EOL;
$showtimes = Showtime::with(['movie', 'room.theater.city'])->limit(10)->get();

foreach ($showtimes as $s) {
    $movieTitle = $s->movie ? $s->movie->title : "NULL";
    $cityName = ($s->room && $s->room->theater && $s->room->theater->city) ? $s->room->theater->city->name : "NULL";
    $out .= "ID: {$s->showtime_id} | MovieID: {$s->movie_id} | Movie: '{$movieTitle}' | City: '{$cityName}' | Start: {$s->start_time}" . PHP_EOL;
    if ($s->showtime_id == 1) {
        $out .= "Raw data (ID 1): " . json_encode($s->toArray()) . PHP_EOL;
    }
}

file_put_contents('showtime_debug.txt', $out);
echo "DEBUG DONE. Written to showtime_debug.txt";
