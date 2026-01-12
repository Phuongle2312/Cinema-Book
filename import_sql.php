<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Starting SQL import from luyenhao_CinemaBooking.sql...\n";

// Read the SQL file
$sqlFile = __DIR__ . '/luyenhao_CinemaBooking.sql';

if (!file_exists($sqlFile)) {
    die("ERROR: SQL file not found at: $sqlFile\n");
}

// Read file content
$sql = file_get_contents($sqlFile);

if ($sql === false) {
    die("ERROR: Could not read SQL file\n");
}

// Convert from UTF-16LE to UTF-8 if needed
if (mb_detect_encoding($sql, 'UTF-16LE', true)) {
    $sql = mb_convert_encoding($sql, 'UTF-8', 'UTF-16LE');
}

// Remove BOM if present
$sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);

// Split by semicolons but be careful with stored procedures
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function ($statement) {
        return !empty($statement) && !preg_match('/^(--|\/\*)/', $statement);
    }
);

echo "Found " . count($statements) . " SQL statements.\n";

// Execute each statement
$successCount = 0;
$errorCount = 0;

foreach ($statements as $index => $statement) {
    try {
        DB::statement($statement);
        $successCount++;
        if (($index + 1) % 10 == 0) {
            echo "Processed " . ($index + 1) . " statements...\n";
        }
    } catch (Exception $e) {
        $errorCount++;
        echo "ERROR on statement " . ($index + 1) . ": " . $e->getMessage() . "\n";
        // Show first 100 chars of problematic statement
        echo "Statement: " . substr($statement, 0, 100) . "...\n\n";
    }
}

echo "\n=== Import Complete ===\n";
echo "Success: $successCount statements\n";
echo "Errors: $errorCount statements\n";

// Show table counts
echo "\n=== Table Row Counts ===\n";
$tables = ['users', 'movies', 'genres', 'cities', 'theaters', 'screens', 'seats', 'showtimes', 'bookings'];
foreach ($tables as $table) {
    try {
        $count = DB::table($table)->count();
        echo "$table: $count rows\n";
    } catch (Exception $e) {
        echo "$table: table not found or error\n";
    }
}
