<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$app->make(Kernel::class)->bootstrap();

try {
    echo "Disabling foreign key checks..." . PHP_EOL;
    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    $tables = DB::select('SHOW TABLES');
    foreach ($tables as $table) {
        $tableName = current((array) $table);
        echo "Dropping table: $tableName" . PHP_EOL;
        DB::statement("DROP TABLE IF EXISTS `$tableName` ");
    }

    echo "Enabling foreign key checks..." . PHP_EOL;
    DB::statement('SET FOREIGN_KEY_CHECKS=1');

    echo "Running migrations..." . PHP_EOL;
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo \Illuminate\Support\Facades\Artisan::output();

    echo "Running seeders..." . PHP_EOL;
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    echo \Illuminate\Support\Facades\Artisan::output();

    echo "DONE." . PHP_EOL;
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
