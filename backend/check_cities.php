<?php

use App\Models\City;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cities = City::all();

echo sprintf("%-5s | %-30s\n", "ID", "Name");
echo str_repeat("-", 40) . "\n";

foreach ($cities as $city) {
    echo sprintf(
        "%-5d | %-30s\n",
        $city->id ?? $city->city_id,
        $city->name
    );
}

// Check specifically for ID 7
$city7 = City::find(7);
if (!$city7) {
    echo "\nCity ID 7 is MISSING!\n";
    // Optional: Attempt to find by name 'Ha Long'
    $halong = City::where('name', 'like', '%Ha Long%')->first();
    if ($halong) {
        echo "Found similar city: " . $halong->name . " (ID: " . ($halong->id ?? $halong->city_id) . ")\n";
    }
}
