<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// 1. Manually create a movie to ensure we have one
$movie = \App\Models\Movie::first();
if (!$movie) {
    $movie = \App\Models\Movie::create([
        'title' => 'Debug Movie',
        'duration' => 120,
        'release_date' => now(),
        'status' => 'now_showing',
        'description' => 'Initial description',
        'slug' => 'debug-movie-' . rand(1000, 9999)
    ]);
}

$movieId = $movie->movie_id;
echo "Testing on Movie ID: $movieId\n";

// 2. Prepare Payload with EMPTY description to test NULLABLE issue
$payload = [
    'title' => $movie->title . ' Updated',
    'duration' => 150,
    'description' => '', // This might be converted to null by middleware
    'synopsis' => '',
    'content' => '',
    'release_date' => '2026-01-01',
    'status' => 'now_showing',
    'poster_url' => 'https://example.com/poster.jpg',
    'is_featured' => true // Boolean sent as boolean
];

// 3. Invoke Controller Update
// We simulate the middleware effect by manually nulling empty strings if we want to be exact,
// but let's just send "" and see if Controller validation handles it.
// Note: Direct controller call bypasses Global Middleware (ConvertEmptyStringsToNull).
// So to strictly reproduce, we should pass null if we suspect that's what's happening.
$payloadWithNulls = $payload;
$payloadWithNulls['description'] = null;
$payloadWithNulls['synopsis'] = null;
$payloadWithNulls['content'] = null;

$request = Illuminate\Http\Request::create(
    "/api/admin/movies/$movieId",
    'PUT',
    $payloadWithNulls // Simulate middleware effect
);
$request->headers->set('Accept', 'application/json');

$controller = new \App\Http\Controllers\Api\Admin\MovieController();

try {
    $response = $controller->update($request, $movieId);
    echo "Status Code: " . $response->getStatusCode() . "\n";
    $content = $response->getContent();
    echo "Content: " . $content . "\n";

    if ($response->getStatusCode() === 422) {
        $errors = json_decode($content, true);
        print_r($errors);
    }
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
