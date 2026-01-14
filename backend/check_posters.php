<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;

$movies = Movie::take(10)->get();
echo "ID | Title | Status | Poster URL\n";
echo "---|---|---|---\n";
foreach ($movies as $movie) {
    echo "{$movie->movie_id} | {$movie->title} | {$movie->status} | " . substr($movie->poster_url, 0, 50) . "...\n";
}
