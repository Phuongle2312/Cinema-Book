<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $movie = App\Models\Movie::first();
    if ($movie) {
        echo "Movie: " . $movie->title . "\n";
        echo "Cast count: " . $movie->cast()->count() . "\n";
    } else {
        echo "No movies found\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
