<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;

$wicked = Movie::where('title', 'like', 'Wike%')->orWhere('title', 'like', 'Wicked%')->first();
if ($wicked) {
    echo "Found movie: " . $wicked->title . "\n";
    $wicked->age_rating = 'T13';
    // Fix title if it is the typo version
    if (strpos($wicked->title, 'Wike') !== false) {
        $wicked->title = 'Wicked: For Good';
        $wicked->slug = Illuminate\Support\Str::slug('Wicked: For Good');
    }
    $wicked->save();
    echo "Updated age rating to T13 and title to Wicked: For Good\n";
} else {
    echo "Wicked movie not found\n";
}
