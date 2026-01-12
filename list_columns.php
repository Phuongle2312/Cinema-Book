<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app->make(Kernel::class)->bootstrap();

try {
    $columns = DB::select('DESCRIBE seats');
    echo "Columns in 'seats' table:" . PHP_EOL;
    foreach ($columns as $column) {
        echo "- " . $column->Field . " (" . $column->Type . ")" . PHP_EOL;
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
