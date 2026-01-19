<?php

use App\Models\Theater;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$theaters = Theater::with('city')->get();

echo "Total Theaters: " . $theaters->count() . "\n";
echo str_repeat("-", 50) . "\n";
echo sprintf("%-5s | %-30s | %-20s | %-10s\n", "ID", "Name", "City", "CityID");
echo str_repeat("-", 50) . "\n";

foreach ($theaters as $theater) {
    echo sprintf(
        "%-5d | %-30s | %-20s | %-10s\n",
        $theater->id ?? $theater->theater_id,
        substr($theater->name, 0, 30),
        $theater->city ? $theater->city->name : 'N/A',
        $theater->city_id ?? 'NULL'
    );
}
