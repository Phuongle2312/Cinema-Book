<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app->make(Kernel::class)->bootstrap();

try {
    $indexes = DB::select('SHOW INDEX FROM rooms');
    echo "Detailed Indexes in 'rooms' table:" . PHP_EOL;
    foreach ($indexes as $index) {
        echo "- INDEX: " . $index->Key_name . " | COLUMN: " . $index->Column_name . " | SEQ: " . $index->Seq_in_index . PHP_EOL;
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
