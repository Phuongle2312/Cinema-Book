<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$app->make(Kernel::class)->bootstrap();

try {
    $tables = DB::select('SHOW TABLES');
    echo "Tables in database:" . PHP_EOL;
    foreach ($tables as $table) {
        $tableName = current((array) $table);
        echo "- " . $tableName . PHP_EOL;
    }

    echo "Check specifically for 'reviews': " . (Schema::hasTable('reviews') ? "EXISTS" : "MISSING") . PHP_EOL;
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
