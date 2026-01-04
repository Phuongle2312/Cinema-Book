<?php
$json = file_get_contents('http://127.0.0.1:8000/api/movies');
$data = json_decode($json, true);
if (isset($data['data'][0])) {
    echo "First Movie Genres: " . json_encode($data['data'][0]['genres']) . PHP_EOL;
} else {
    echo "No movie found or invalid structure." . PHP_EOL;
}
