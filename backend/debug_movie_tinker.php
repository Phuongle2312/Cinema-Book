<?php

use App\Models\Movie;
use App\Http\Controllers\Api\Admin\MovieController;
use Illuminate\Http\Request;
use Illuminate\Container\Container;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

// 1. Find Movie
$movie = Movie::first();
if (!$movie) {
    echo "No movie found. Creating one...\n";
    $movie = Movie::create([
        'title' => 'Debug Movie',
        'duration' => 120,
        'release_date' => now(),
        'status' => 'now_showing',
        'description' => 'Initial',
        'slug' => 'debug-movie-' . rand(1000, 9999)
    ]);
}
echo "Testing with Movie ID: " . $movie->movie_id . "\n";

// 2. Prepare Validated Payload (Simulating what passes validation)
// Note: We are calling the controller method directly.
// The controller method starts with $validator = Validator::make(...).
// So we need to pass a Request populated with all fields.

$payload = [
    'title' => 'Updated Title',
    'age_rating' => 'P',
    'duration' => 150,
    'release_date' => '2026-06-01',
    'status' => 'coming_soon',
    'description' => null,   // Testing null
    'synopsis' => 'New Synopsis',
    'content' => 'New Content',
    'poster_url' => 'https://example.com/poster.jpg',
    'banner_url' => 'https://example.com/banner.jpg',
    'trailer_url' => 'https://youtube.com',
    'is_featured' => true,
    'extra_field' => 'should_be_ignored'
];

$request = Request::create('/api/admin/movies/' . $movie->movie_id, 'PUT', $payload);
$request->headers->set('Accept', 'application/json');

// 3. Invoke Controller
$controller = new MovieController();

try {
    echo "Invoking update...\n";
    $response = $controller->update($request, $movie->movie_id);

    echo "Response Status: " . $response->getStatusCode() . "\n";
    $content = $response->getContent();
    echo "Response Content: " . $content . "\n";

    if ($response->getStatusCode() !== 200) {
        $json = json_decode($content, true);
        if (isset($json['errors'])) {
            echo "Validation Errors:\n";
            print_r($json['errors']);
        }
    } else {
        echo "SUCCESS! Movie updated.\n";
    }

} catch (\Exception $e) {
    echo "EXCEPTION CAUGHT: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
