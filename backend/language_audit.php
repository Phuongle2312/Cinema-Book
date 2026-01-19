<?php

use App\Models\Movie;
use App\Models\Theater;
use App\Models\Offer;
use App\Models\City;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function hasVietnamese($str)
{
    if (!$str)
        return false;
    return preg_match('/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/iu', $str);
}

echo "Scanning for Vietnamese content...\n\n";

// 1. Movies
$movies = Movie::all();
echo "--- Movies (" . $movies->count() . ") ---\n";
foreach ($movies as $m) {
    if (hasVietnamese($m->title) || hasVietnamese($m->description)) {
        echo "[ID: {$m->id}] Found Vietnamese: " . substr($m->title, 0, 50) . "...\n";
    }
}

// 2. Theaters
$theaters = Theater::all();
echo "\n--- Theaters (" . $theaters->count() . ") ---\n";
foreach ($theaters as $t) {
    if (hasVietnamese($t->name) || hasVietnamese($t->address)) {
        // Address might be Viet, that's okay, but check name
        echo "[ID: {$t->id}] " . $t->name . "\n";
    }
}

// 3. Offers
$offers = Offer::all(); // Assuming Model exists
echo "\n--- Offers (" . $offers->count() . ") ---\n";
foreach ($offers as $o) {
    if (hasVietnamese($o->title) || hasVietnamese($o->description)) {
        echo "[ID: {$o->id}] " . $o->title . "\n";
    }
}

echo "\nScan Complete.\n";
