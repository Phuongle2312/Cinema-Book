<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Language;
use Illuminate\Support\Facades\DB;

echo "--- CHECKING LANGUAGES ---\n";
$langs = Language::all();
foreach ($langs as $l) {
    echo "ID: {$l->language_id} | Name: {$l->name}\n";
    echo " - Movies Count: " . DB::table('movie_language')->where('language_id', $l->language_id)->count() . "\n";
}
