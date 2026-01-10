<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Attempting to fix `seats` table schema...\n";

try {
    // 1. Fix Primary Key and Auto Increment
    $hasPrimaryKey = DB::select("SHOW KEYS FROM seats WHERE Key_name = 'PRIMARY'");
    
    if (empty($hasPrimaryKey)) {
        echo "Adding PRIMARY KEY and AUTO_INCREMENT...\n";
        DB::statement('ALTER TABLE seats MODIFY seat_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY');
    } else {
        echo "Primary Key exists. Ensuring AUTO_INCREMENT...\n";
        DB::statement('ALTER TABLE seats MODIFY seat_id BIGINT UNSIGNED AUTO_INCREMENT');
    }

    // 2. Add extra_price column if missing
    if (!Schema::hasColumn('seats', 'extra_price')) {
        echo "Adding `extra_price` column...\n";
        Schema::table('seats', function (Blueprint $table) {
            $table->decimal('extra_price', 10, 0)->default(0)->after('seat_type');
        });
    } else {
        echo "`extra_price` column already exists.\n";
    }
    
    echo "Seats schema fixed successfully.\n";
} catch (\Exception $e) {
    echo "Error fixing schema: " . $e->getMessage() . "\n";
}
