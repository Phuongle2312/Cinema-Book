<?php

use App\Models\Theater;
use App\Models\City;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$problematicTheaters = [
    'CGV Sense City',
    'CGV AEON Mall Hai Phong'
];

foreach ($problematicTheaters as $name) {
    $theater = Theater::where('name', 'like', "%$name%")->first();
    if ($theater) {
        echo "Theater: " . $theater->name . " (ID: " . $theater->id . ")\n";
        echo " - City ID: " . ($theater->city_id ?? 'NULL') . "\n";

        if ($theater->city_id) {
            $city = City::find($theater->city_id);
            if ($city) {
                echo " - City Found: " . $city->name . "\n";
            } else {
                echo " - City NOT FOUND in DB!\n";
            }
        }
    } else {
        echo "Theater '$name' not found in DB.\n";
    }
    echo "--------------------------------------------------\n";
}
