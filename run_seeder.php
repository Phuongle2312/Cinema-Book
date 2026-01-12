<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use Database\Seeders\CinemaSeeder;

$app->make(Kernel::class)->bootstrap();

try {
    $seeder = new CinemaSeeder();
    $seeder->run();
    echo "Seeding completed successfully!" . PHP_EOL;
} catch (Throwable $e) {
    echo "SEEDING ERROR: " . $e->getMessage() . PHP_EOL;
    echo "FILE: " . $e->getFile() . " on line " . $e->getLine() . PHP_EOL;
    echo "TRACE: " . $e->getTraceAsString() . PHP_EOL;
}
