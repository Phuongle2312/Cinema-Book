<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app->make(Kernel::class)->bootstrap();

try {
    $keys = DB::select("SHOW KEYS FROM rooms WHERE Non_unique = 0");
    echo "Unique Keys in 'rooms' table:" . PHP_EOL;
    foreach ($keys as $key) {
        echo "- KEY: " . $key->Key_name . " | COLUMN: " . $key->Column_name . PHP_EOL;
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
