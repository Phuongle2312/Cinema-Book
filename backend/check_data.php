<?php

use App\Models\Movie;

// Check if there are any movies
$count = Movie::count();
echo "Total Movies: $count\n";

if ($count > 0) {
    echo "First 5 movies status:\n";
    $movies = Movie::take(5)->get(['title', 'status']);
    foreach ($movies as $movie) {
        echo " - {$movie->title}: {$movie->status}\n";
    }
} else {
    echo "No movies found in database.\n";
}
