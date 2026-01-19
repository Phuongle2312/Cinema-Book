<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;

$movie = Movie::where('title', 'like', 'Chainsaw Man%')->first();
if ($movie) {
    echo "Found movie: " . $movie->title . "\n";
    $movie->age_rating = 'T18';
    $movie->save();
    echo "Updated age rating to T18\n";
} else {
    echo "Chainsaw Man movie not found\n";
}

$zootopia = Movie::where('title', 'like', 'Zootopia%')->first();
if ($zootopia) {
    echo "Found movie: " . $zootopia->title . "\n";
    $zootopia->age_rating = 'K';
    $zootopia->save();
    echo "Updated age rating to K\n";
}

$wicked = Movie::where('title', 'like', 'Wicked%')->first();
if ($wicked) {
    echo "Found movie: " . $wicked->title . "\n";
    $wicked->age_rating = 'T13';
    $wicked->save();
    echo "Updated age rating to T13\n";
}
