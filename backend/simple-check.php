<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=cinema_booking', 'root', '');
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

$required = ['users', 'genres', 'languages', 'cast', 'theaters', 'screens', 'movies', 
             'movie_genre', 'movie_language', 'movie_cast', 'showtimes', 'seats',
             'bookings', 'booking_seats', 'seat_locks', 'transactions', 'reviews'];

$missing = array_diff($required, $tables);
$extra = array_diff($tables, $required, ['migrations', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens', 'sessions', 'personal_access_tokens']);

echo "REQUIRED: " . count($required) . "\n";
echo "FOUND: " . count(array_intersect($required, $tables)) . "\n";
echo "MISSING: " . count($missing) . "\n";

if (count($missing) > 0) {
    echo "\nMISSING TABLES:\n";
    foreach ($missing as $t) echo "- $t\n";
}

if (count($extra) > 0) {
    echo "\nEXTRA TABLES:\n";
    foreach ($extra as $t) echo "- $t\n";
}

echo "\nALL TABLES:\n";
foreach ($tables as $t) echo "$t\n";
