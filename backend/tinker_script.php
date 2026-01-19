$movie = App\Models\Movie::first();
echo "Movie ID: " . $movie->movie_id . "\n";

$payload = [
'title' => 'Updated via Tinker',
'age_rating' => 'P',
'duration' => 120,
'release_date' => '2026-06-01',
'status' => 'now_showing',
'description' => null, // Testing NULL description
'synopsis' => 'Synopsis',
'content' => 'Content'
];

$request = Illuminate\Http\Request::create('/api/admin/movies/' . $movie->movie_id, 'PUT', $payload);
$request->headers->set('Accept', 'application/json');

$controller = new App\Http\Controllers\Api\Admin\MovieController();

try {
$response = $controller->update($request, $movie->movie_id);
echo "Response Code: " . $response->getStatusCode() . "\n";
echo $response->getContent();
} catch (\Exception $e) {
echo "Error: " . $e->getMessage();
}
exit();