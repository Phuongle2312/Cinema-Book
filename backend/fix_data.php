<?php

use App\Models\City;
use App\Models\Theater;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cityId = 7;
$cityName = 'Ha Long';

$city = City::find($cityId);
if (!$city) {
    echo "Creating missing City ID $cityId ($cityName)...\n";
    // Check if ID is auto-increment or if we can force it.
    // Usually standard Eloquent create doesn't force ID unless we specify it and if allow.
    // Better to update the theater to a new city IF we can't force ID, OR force ID.

    try {
        DB::table('cities')->insert([
            'city_id' => $cityId,
            'name' => $cityName,
            'slug' => 'ha-long',
            'country' => 'Vietnam', // Add default fields if needed
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "City created.\n";
    } catch (\Exception $e) {
        echo "Error inserting city: " . $e->getMessage() . "\n";
    }
} else {
    echo "City ID $cityId already exists.\n";
}

// Verify Theater
$theater = Theater::with('city')->find(18); // CGV Vincom Ha Long
if ($theater) {
    echo "Theater ID 18 City: " . ($theater->city ? $theater->city->name : 'STILL N/A') . "\n";
}
