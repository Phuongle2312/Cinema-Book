<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking MySQL Databases ===\n\n";

try {
    // List all databases
    $databases = DB::select('SHOW DATABASES');
    echo "Available databases:\n";
    foreach ($databases as $db) {
        $dbName = $db->Database;
        echo "  - $dbName\n";
    }

    echo "\n=== Current Database ===\n";
    $currentDb = DB::select('SELECT DATABASE() as db')[0]->db;
    echo "Current database: $currentDb\n\n";

    // List all tables in current database
    echo "=== Tables in '$currentDb' ===\n";
    $tables = DB::select('SHOW TABLES');

    if (empty($tables)) {
        echo "No tables found in current database.\n\n";

        // Check if luyenhao_CinemaBooking database exists
        echo "Checking for 'luyenhao_CinemaBooking' database...\n";
        $hasLuyenhao = false;
        foreach ($databases as $db) {
            if ($db->Database === 'luyenhao_CinemaBooking') {
                $hasLuyenhao = true;
                break;
            }
        }

        if ($hasLuyenhao) {
            echo "\nFound 'luyenhao_CinemaBooking' database!\n";
            echo "Listing tables in that database...\n\n";

            DB::statement('USE luyenhao_CinemaBooking');
            $tables = DB::select('SHOW TABLES');

            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                $count = DB::table($tableName)->count();
                echo "  - $tableName ($count rows)\n";
            }
        }
    } else {
        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            $count = DB::table($tableName)->count();
            echo "  - $tableName ($count rows)\n";
        }
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
