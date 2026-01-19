<?php

use Illuminate\Support\Facades\DB;
use App\Models\Offer;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Checking 'offers' table existence...\n";
    $tables = DB::select('SHOW TABLES LIKE "offers"');
    if (empty($tables)) {
        echo "Table 'offers' DOES NOT EXIST.\n";
        $promotions = DB::select('SHOW TABLES LIKE "promotions"');
        if (!empty($promotions)) {
            echo "Table 'promotions' EXISTS. Model might need adjustment.\n";
        } else {
            echo "Neither 'offers' a nor 'promotions' found.\n";
        }
    } else {
        echo "Table 'offers' EXISTS.\n";
        $columns = DB::getSchemaBuilder()->getColumnListing('offers');
        print_r($columns);

        echo "\nTesting Offer Model count:\n";
        echo Offer::count();
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
