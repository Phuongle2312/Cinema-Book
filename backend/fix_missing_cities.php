<?php

use App\Models\City;
use App\Models\Theater;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking for missing cities...\n";

// Map ID to Name (Manual Knowledge Base)
$knownCities = [
    4 => 'Can Tho',
    6 => 'Hai Phong', // Assuming 6 is Hai Phong based on gaps
    7 => 'Ha Long',
    8 => 'Hue',
    // Add more if needed
];

$theaters = Theater::all();
$fixedCount = 0;

foreach ($theaters as $theater) {
    if (!$theater->city_id)
        continue;

    $city = City::where('city_id', $theater->city_id)->first();

    if (!$city) {
        $missingId = $theater->city_id;
        $params = $knownCities[$missingId] ?? "City-{$missingId}";
        $cityName = is_array($params) ? $params['name'] : $params;

        echo "Theater [{$theater->name}] points to missing City ID: {$missingId}. Creating '{$cityName}'...\n";

        try {
            DB::table('cities')->insert([
                'city_id' => $missingId,
                'name' => $cityName,
                'slug' => \Illuminate\Support\Str::slug($cityName),
                'country' => 'Vietnam',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo " -> Created City: {$cityName} (ID: {$missingId})\n";
            $fixedCount++;
        } catch (\Exception $e) {
            echo " -> Error creating city: " . $e->getMessage() . "\n";
        }
    }
}

if ($fixedCount === 0) {
    echo "No missing cities found.\n";
} else {
    echo "Fixed {$fixedCount} missing cities.\n";
}
