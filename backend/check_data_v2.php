<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;

try {
    // Check if there are any movies
    $count = Movie::count();
    $total = $count;
    echo "Total Movies: $count\n\n";

    if ($count > 0) {
        // Check filtering logic
        $nowShowing = Movie::where('status', 'now_showing')->count();
        $comingSoon = Movie::where('status', 'coming_soon')->count();
        
        echo "Now Showing: $nowShowing\n";
        echo "Coming Soon: $comingSoon\n";

        echo "\nFirst 5 movies status:\n";
        $movies = Movie::take(5)->get(['title', 'status']);
        foreach ($movies as $movie) {
            echo " - {$movie->title}: {$movie->status}\n";
        }
    } else {
        echo "No movies found in database.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
