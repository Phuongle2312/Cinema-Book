<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app->make(Kernel::class)->bootstrap();

try {
    $db = DB::connection()->getDatabaseName();
    $host = DB::connection()->getConfig('host');
    echo "CONNECTED TO DATABASE: " . $db . " on HOST: " . $host . PHP_EOL;

    $tables = DB::select("SHOW TABLES");
    echo "TABLES IN DATABASE:" . PHP_EOL;
    foreach ($tables as $table) {
        $name = current((array) $table);
        if ($name === 'rooms') {
            $count = DB::table('rooms')->count();
            echo "- rooms (Count: $count)" . PHP_EOL;
        } else {
            // echo "- " . $name . PHP_EOL;
        }
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
