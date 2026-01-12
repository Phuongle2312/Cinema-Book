<?php
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $counts = DB::table('showtimes')
        ->select('movie_id', DB::raw('count(*) as total'))
        ->groupBy('movie_id')
        ->get();

    echo "Showtimes per Movie:\n";
    foreach ($counts as $c) {
        $title = DB::table('movies')->where('movie_id', $c->movie_id)->value('title');
        echo "Movie ID {$c->movie_id}: {$title} - {$c->total} showtimes\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
