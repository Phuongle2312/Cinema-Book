<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$movie = App\Models\Movie::first();
if ($movie) {
    echo "COLUMNS: " . json_encode(array_keys($movie->getAttributes())) . PHP_EOL;
    echo "DATA: " . json_encode($movie) . PHP_EOL;
} else {
    echo "NO MOVIE FOUND" . PHP_EOL;
}
