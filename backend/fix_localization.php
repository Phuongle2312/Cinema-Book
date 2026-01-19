<?php

use App\Models\Movie;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$updates = [
    13 => [
        'title' => 'The Love of Siam',
        'description' => 'A romantic drama about two boyhood friends whose lives are turned upside down when they reunite in Siam Square.'
    ],
    14 => [
        'title' => 'Kisaragi Station',
        'description' => 'A horror film based on the famous urban legend about a mysterious train station that does not exist on any map.'
    ],
    16 => [
        'title' => 'Princess Mononoke',
        'description' => 'Ashitaka finds himself in the middle of a war between the forest gods and Tatara, a mining colony. In this quest he also meets San, the Mononoke Hime.'
    ]
];

foreach ($updates as $id => $data) {
    $movie = Movie::find($id);
    if ($movie) {
        $movie->update($data);
        echo "Updated Movie ID $id: {$data['title']}\n";
    } else {
        echo "Movie ID $id not found.\n";
    }
}
echo "Localization Update Complete.\n";
