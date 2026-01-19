<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/api/admin/movies', 'GET', ['page' => 1])
);

$content = $response->getContent();
$data = json_decode($content, true);

if (empty($data['data']['data'])) {
    echo "No movies found to test update on.\n";
    exit;
}

$movie = $data['data']['data'][0];
$movieId = $movie['movie_id'] ?? $movie['id'];

echo "Found Movie ID: $movieId. Title: {$movie['title']}\n";

// Prepare Update Payload
// mimicking frontend ManageMovies.js
$payload = [
    'title' => $movie['title'],
    'age_rating' => 'P',
    'duration' => 120, // Changing duration
    'release_date' => '2026-05-20',
    'status' => 'now_showing',
    'description' => 'Test description',
    'synopsis' => 'Test synopsis',
    'content' => 'Test content details',
    'poster_url' => 'https://example.com/poster.jpg',
    'banner_url' => 'https://example.com/banner.jpg',
    'trailer_url' => 'https://youtube.com/watch?v=123',
    'is_featured' => 1,
    'extra_field_that_should_not_exist' => 'hacking' // Test if this crashes it
];

// Mock Admin User
$admin = \App\Models\User::where('role', 'admin')->first();
if (!$admin) {
    // Create temp admin
    $admin = \App\Models\User::first(); // Fallback
}

echo "Acting as User ID: " . ($admin ? $admin->id : 'Guest') . "\n";

// Create Request
$request = Illuminate\Http\Request::create(
    "/api/admin/movies/$movieId",
    'PUT',
    $payload
);

$request->headers->set('Accept', 'application/json');
if ($admin) {
    // Manually login or just bypass middleware?
    // Doing a real request via Kernel is hard with auth middleware unless we mock it.
    // Let's rely on internal controller call directly to skip middleware or use ActingAs.
    // But middleware is baked into route pipeline.
}

// Easier way: Use Artisan tinker or direct controller call
$controller = new \App\Http\Controllers\Api\Admin\MovieController();
try {
    $response = $controller->update($request, $movieId);
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
