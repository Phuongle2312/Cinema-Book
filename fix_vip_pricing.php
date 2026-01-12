<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Fixing VIP Seat Pricing ===\n\n";

try {
    // Check if extra_price column exists
    $columns = DB::select("SHOW COLUMNS FROM seats LIKE 'extra_price'");

    if (empty($columns)) {
        echo "❌ Column 'extra_price' does not exist in seats table.\n";
        echo "Adding extra_price column...\n";

        DB::statement("ALTER TABLE seats ADD COLUMN extra_price DECIMAL(10,0) DEFAULT 0 AFTER seat_type");
        echo "✅ Column added!\n\n";
    }

    // Update VIP seats
    echo "Updating VIP seats (extra_price = 10000)...\n";
    $vipCount = DB::table('seats')->where('seat_type', 'vip')->update(['extra_price' => 10000]);
    echo "Updated $vipCount VIP seats\n\n";

    // Update Couple seats
    echo "Updating Couple seats (extra_price = 20000)...\n";
    $coupleCount = DB::table('seats')->where('seat_type', 'couple')->update(['extra_price' => 20000]);
    echo "Updated $coupleCount Couple seats\n\n";

    // Update Standard seats
    echo "Updating Standard seats (extra_price = 0)...\n";
    $standardCount = DB::table('seats')->where('seat_type', 'standard')->update(['extra_price' => 0]);
    echo "Updated $standardCount Standard seats\n\n";

    // Verify
    echo "=== Verification ===\n";
    $vipSample = DB::table('seats')->where('seat_type', 'vip')->first();
    $coupleSample = DB::table('seats')->where('seat_type', 'couple')->first();
    $standardSample = DB::table('seats')->where('seat_type', 'standard')->first();

    if ($vipSample) {
        echo "VIP Seat Sample: Row {$vipSample->row}, extra_price = " . ($vipSample->extra_price ?? 'NULL') . "\n";
    }
    if ($coupleSample) {
        echo "Couple Seat Sample: Row {$coupleSample->row}, extra_price = " . ($coupleSample->extra_price ?? 'NULL') . "\n";
    }
    if ($standardSample) {
        echo "Standard Seat Sample: Row {$standardSample->row}, extra_price = " . ($standardSample->extra_price ?? 'NULL') . "\n";
    }

    echo "\n✅ VIP seat pricing fixed successfully!\n";
    echo "\nWith base_price = 90,000:\n";
    echo "  - VIP seats: 90,000 + 10,000 = 100,000 VND ✓\n";
    echo "  - Couple seats: 90,000 + 20,000 = 110,000 VND ✓\n";
    echo "  - Standard seats: 90,000 + 0 = 90,000 VND ✓\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
