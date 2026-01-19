<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;
use App\Models\Showtime;

$conan = Movie::where('title', 'like', '%Conan%')->first();

if (!$conan) {
    echo "Conan NOT FOUND.\n";
    exit;
}

echo "Movie: {$conan->title} (ID: {$conan->movie_id})\n";
$count = Showtime::where('movie_id', $conan->movie_id)->count();
echo "Showtimes Count: {$count}\n";

if ($count > 0) {
    $shows = Showtime::where('movie_id', $conan->movie_id)
        ->orderBy('start_time')
        ->take(5)
        ->get();
    foreach ($shows as $show) {
        echo " - {$show->start_time} (Room: {$show->room_id})\n";
    }
} else {
    echo "No showtimes found!\n";
}
