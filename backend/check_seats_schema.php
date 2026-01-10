<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking `seats` table schema...\n";

$columns = DB::select('DESCRIBE seats');
foreach ($columns as $col) {
    echo "Field: {$col->Field} | Type: {$col->Type} | Null: {$col->Null} | Key: {$col->Key} | Default: {$col->Default} | Extra: {$col->Extra}\n";
}
