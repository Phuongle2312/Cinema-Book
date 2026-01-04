<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $movies = \App\Models\Movie::with(['genres', 'languages'])
        ->whereIn('status', ['now_showing', 'coming_soon'])
        ->where('rating', '>=', 7.0)
        ->orderBy('rating', 'desc')
        ->limit(10)
        ->get();

    echo "Query Successful!\n";
    echo "Count: " . $movies->count() . "\n";
    foreach ($movies as $movie) {
        echo $movie->title . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
