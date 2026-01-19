<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Columns of 'showtimes' table:\n";
    $columns = DB::getSchemaBuilder()->getColumnListing('showtimes');
    print_r($columns);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
