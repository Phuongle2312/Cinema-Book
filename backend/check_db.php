<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    echo array_values((array) $table)[0].PHP_EOL;
}

echo "\nChecking offers table: ".(Schema::hasTable('offers') ? 'Exists' : 'Missing').PHP_EOL;
if (Schema::hasTable('offers')) {
    $count = DB::table('offers')->count();
    echo 'Total offers: '.$count.PHP_EOL;
}
echo 'Checking promotions table: '.(Schema::hasTable('promotions') ? 'Exists' : 'Missing').PHP_EOL;
