<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $columns = DB::select('DESCRIBE cast');
    foreach ($columns as $col) {
        echo $col->Field . " (" . $col->Type . ")\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
