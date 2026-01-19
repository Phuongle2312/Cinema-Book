<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Seat;
use Illuminate\Support\Facades\DB;

echo "--- CHECKING SEAT PRICES ---\n";
try {
    $stats = Seat::select('type', DB::raw('COUNT(*) as count'), DB::raw('AVG(extra_price) as avg_extra_price'))
        ->groupBy('type')
        ->get();

    foreach ($stats as $stat) {
        echo "Type: {$stat->type} | Count: {$stat->count} | Avg Extra Price: {$stat->avg_extra_price}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
