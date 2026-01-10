<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function fixTableSchema($table, $pk) {
    echo "Attempting to fix `{$table}` table schema...\n";
    try {
        $hasPrimaryKey = DB::select("SHOW KEYS FROM {$table} WHERE Key_name = 'PRIMARY'");
        
        if (empty($hasPrimaryKey)) {
            echo "Adding PRIMARY KEY and AUTO_INCREMENT to {$table}...\n";
            DB::statement("ALTER TABLE {$table} MODIFY {$pk} BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY");
        } else {
            echo "Primary Key exists. Ensuring AUTO_INCREMENT on {$table}...\n";
            DB::statement("ALTER TABLE {$table} MODIFY {$pk} BIGINT UNSIGNED AUTO_INCREMENT");
        }
        echo "{$table} schema fixed successfully.\n";
    } catch (\Exception $e) {
        echo "Error fixing {$table}: " . $e->getMessage() . "\n";
    }
}

fixTableSchema('theaters', 'theater_id');
fixTableSchema('cities', 'city_id');
