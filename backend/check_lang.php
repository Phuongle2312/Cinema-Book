<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;
use App\Models\Cast;
use App\Models\User;

echo "--- CHECKING DATA LANGUAGE ---\n";

$movie = Movie::first();
if ($movie) {
    echo "Movie Title: " . $movie->title . "\n";
    echo "Movie Desc: " . \Illuminate\Support\Str::limit($movie->description, 50) . "\n";
} else {
    echo "No movies found.\n";
}

$user = User::latest()->first();
if ($user) {
    echo "Latest User: " . $user->name . "\n";
}

$cast = Cast::first();
if ($cast) {
    echo "Cast Name: " . $cast->name . "\n";
}
