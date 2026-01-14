<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Attempting to fix `showtimes` table schema...\n";

try {
    // Check if Primary Key exists
    $hasPrimaryKey = DB::select("SHOW KEYS FROM showtimes WHERE Key_name = 'PRIMARY'");

    if (empty($hasPrimaryKey)) {
        echo "Adding PRIMARY KEY and AUTO_INCREMENT...\n";
        DB::statement('ALTER TABLE showtimes MODIFY showtime_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY');
    } else {
        echo "Primary Key exists. Ensuring AUTO_INCREMENT...\n";
        DB::statement('ALTER TABLE showtimes MODIFY showtime_id BIGINT UNSIGNED AUTO_INCREMENT');
    }

    echo "Schema fixed successfully.\n";
} catch (\Exception $e) {
    echo 'Error fixing schema: '.$e->getMessage()."\n";
}
