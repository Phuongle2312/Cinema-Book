<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;
use Illuminate\Support\Facades\DB;

echo "--- CHECKING MOVIE DETAILS ---\n";
$movies = Movie::with('cast')->limit(5)->get();

foreach ($movies as $m) {
    echo "ID: {$m->movie_id} | Title: {$m->title}\n";
    echo "Desc: " . substr($m->description, 0, 50) . "...\n";
    echo "Cast Count: " . $m->cast->count() . "\n";
    if ($m->cast->count() > 0) {
        foreach ($m->cast->take(3) as $c) {
            echo " - " . $c->name . " ({$c->pivot->role})\n";
        }
    } else {
        echo " - NO CAST DATA\n";
    }
    echo "------------------------\n";
}
