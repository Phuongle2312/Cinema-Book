<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\Admin\AdminDashboardController;
use Illuminate\Http\Request;

try {
    $controller = new AdminDashboardController();
    $response = $controller->stats();

    // Dump the data structure
    $data = $response->getData(true); // Get as array

    echo "Success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
    echo "Stats Bookings: " . $data['data']['stats']['bookings']['value'] . "\n";
    echo "Charts Revenue Count: " . count($data['data']['charts']['revenue']) . "\n";
    echo "Top Movies Count: " . count($data['data']['charts']['top_movies']) . "\n";

    // Print first item of revenue chart to see weird date formats if any
    if (!empty($data['data']['charts']['revenue'])) {
        echo "Sample Revenue Date: " . $data['data']['charts']['revenue'][0]['date'] . "\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
