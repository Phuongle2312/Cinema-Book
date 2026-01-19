<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Seat;

try {
    $vipCount = Seat::where('type', 'vip')->update(['extra_price' => 20000]);
    $coupleCount = Seat::where('type', 'couple')->update(['extra_price' => 30000]);
    $standardCount = Seat::where('type', 'standard')->update(['extra_price' => 0]);

    echo "Updated Seat Prices:\n";
    echo "- VIP: $vipCount seats (20,000 VND)\n";
    echo "- Couple: $coupleCount seats (30,000 VND)\n";
    echo "- Standard: $standardCount seats (0 VND)\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
