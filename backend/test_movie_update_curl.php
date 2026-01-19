<?php

$url = 'http://127.0.0.1:8000/api/admin/movies/1'; // Assuming ID 1 exists, or we will query list first
$listUrl = 'http://127.0.0.1:8000/api/admin/movies';

// 1. Get List to find a valid ID
$ch = curl_init($listUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
echo "Raw Response: " . substr($response, 0, 500) . "...\n"; // Preview first 500 chars

if (empty($data['data']['data'])) {
    die("No movies found via API. Check Raw Response above.\n");
}

$firstMovie = $data['data']['data'][0];
$id = $firstMovie['movie_id'] ?? $firstMovie['id'];
echo "Targeting Movie ID: $id\n";
$url = "http://127.0.0.1:8000/api/admin/movies/$id";

// 2. Send PUT request
$payload = [
    'title' => 'Updated via Curl',
    'age_rating' => 'P',
    'duration' => 120,
    'release_date' => '2026-05-20',
    'status' => 'now_showing',
    'description' => '', // Empty string to test nullable
    'synopsis' => '',
    'content' => '',
    'poster_url' => 'https://example.com/poster.jpg',
    'is_featured' => 1
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
// Note: Frontend sends JSON usually, but standard form data might be used.
// Let's force JSON as React usually does.
$jsonData = json_encode($payload);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

echo "Sending Payload: $jsonData\n";

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $result\n";
