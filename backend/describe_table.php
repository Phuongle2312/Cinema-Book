<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select('DESCRIBE showtimes');
$output = "";
foreach ($columns as $col) {
    $output .= "{$col->Field} | Type: {$col->Type} | Null: {$col->Null} | Default: {$col->Default}\n";
}
file_put_contents('schema.txt', $output);
echo "Schema written to schema.txt";
