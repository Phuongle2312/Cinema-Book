<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app->make(Kernel::class)->bootstrap();

try {
    $indexes = DB::select('SHOW INDEX FROM rooms');
    echo "Indexes in 'rooms' table:" . PHP_EOL;
    foreach ($indexes as $index) {
        echo "- " . $index->Key_name . " (" . $index->Column_name . ")" . PHP_EOL;
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
