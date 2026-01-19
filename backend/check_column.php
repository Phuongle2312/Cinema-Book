<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$exists = DB::getSchemaBuilder()->hasColumn('showtimes', 'end_time');
echo $exists ? "YES end_time exists" : "NO end_time does not exist";
