<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app->make(Kernel::class)->bootstrap();

try {
    $tables = DB::select('SHOW TABLES');
    echo "Tables in database:" . PHP_EOL;
    foreach ($tables as $table) {
        foreach ($table as $key => $value) {
            echo "- " . $value . PHP_EOL;
        }
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
