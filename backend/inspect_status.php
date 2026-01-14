<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;

$movies = Movie::all(['movie_id', 'title', 'status']);
echo "ID | Title | Status (Raw Length)\n";
echo "---|---|---\n";
foreach ($movies as $movie) {
    $len = strlen($movie->status);
    echo "{$movie->movie_id} | {$movie->title} | '{$movie->status}' ($len)\n";
}
