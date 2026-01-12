<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Copying Data from luyenhao_CinemaBooking ===\n\n";

// Get current database name
$currentDb = DB::connection()->getDatabaseName();
echo "Target database: $currentDb\n";
echo "Source database: luyenhao_CinemaBooking\n\n";

try {
    // Tables to copy (in order to respect foreign keys)
    $tablesToCopy = [
        'users',
        'genres',
        'languages',
        'cast',
        'cities',
        'movies',
        'movie_genre',
        'movie_language',
        'movie_cast',
        'theaters',
        'screens',
        'seats',
        'showtimes',
        'bookings',
        'booking_seats',
        'seat_locks',
        'transactions',
        'reviews',
        'combos',
        'booking_combos'
    ];

    foreach ($tablesToCopy as $table) {
        echo "Copying table: $table... ";

        try {
            // Check if table exists in source
            $exists = DB::select("SELECT COUNT(*) as count FROM information_schema.tables 
                                  WHERE table_schema = 'luyenhao_CinemaBooking' 
                                  AND table_name = '$table'")[0]->count;

            if ($exists == 0) {
                echo "SKIPPED (table not found in source)\n";
                continue;
            }

            // Check if table exists in target
            $targetExists = DB::select("SELECT COUNT(*) as count FROM information_schema.tables 
                                        WHERE table_schema = '$currentDb' 
                                        AND table_name = '$table'")[0]->count;

            if ($targetExists == 0) {
                echo "SKIPPED (table not found in target)\n";
                continue;
            }

            // Copy data using INSERT INTO SELECT
            DB::statement("SET FOREIGN_KEY_CHECKS = 0");
            DB::statement("TRUNCATE TABLE `$currentDb`.`$table`");
            DB::statement("INSERT INTO `$currentDb`.`$table` SELECT * FROM `luyenhao_CinemaBooking`.`$table`");

            $count = DB::table($table)->count();
            echo "SUCCESS ($count rows)\n";

        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    }

    DB::statement("SET FOREIGN_KEY_CHECKS = 1");

    echo "\n=== Summary ===\n";
    foreach (['users', 'movies', 'theaters', 'screens', 'seats', 'showtimes', 'bookings'] as $table) {
        try {
            $count = DB::table($table)->count();
            echo "$table: $count rows\n";
        } catch (Exception $e) {
            echo "$table: error\n";
        }
    }

    echo "\n✅ Data copy complete!\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
