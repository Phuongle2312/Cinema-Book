<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    if (Schema::hasTable('migrations')) {
        echo "Migrations table exists. Repairing...\n";

        // 1. Fix IDs to be unique and sequential
        echo "Renumbering IDs...\n";
        DB::statement('SET @i=0');
        DB::statement('UPDATE migrations SET id = (@i:=@i+1)');
        echo "IDs renumbered.\n";

        // 2. Ensure ID is Primary Key
        try {
            DB::statement('ALTER TABLE migrations ADD PRIMARY KEY (id)');
            echo "Primary Key added to id.\n";
        } catch (\Exception $e) {
            echo "Primary Key add failed (might already exist): " . $e->getMessage() . "\n";
        }

        // 3. Add AUTO_INCREMENT
        try {
            DB::statement('ALTER TABLE migrations MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT');
            echo "AUTO_INCREMENT added to id.\n";
        } catch (\Exception $e) {
            echo "AUTO_INCREMENT add failed: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "Migrations table does not exist.\n";
    }
} catch (\Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
