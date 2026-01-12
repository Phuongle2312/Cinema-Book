<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================================\n";
echo "   CINEMA BOOK - MASTER DATABASE SETUP SCRIPT\n";
echo "==========================================================\n\n";

$currentDb = DB::connection()->getDatabaseName();
echo "📊 Current Database: $currentDb\n";
echo "📁 Source Database: luyenhao_CinemaBooking\n\n";

// Step 1: Check if luyenhao_CinemaBooking exists
echo "Step 1: Checking source database...\n";
try {
    $databases = DB::select('SHOW DATABASES');
    $hasSource = false;
    foreach ($databases as $db) {
        if ($db->Database === 'luyenhao_CinemaBooking') {
            $hasSource = true;
            break;
        }
    }

    if (!$hasSource) {
        die("❌ ERROR: Database 'luyenhao_CinemaBooking' not found!\n");
    }
    echo "✅ Source database found!\n\n";
} catch (Exception $e) {
    die("❌ ERROR: " . $e->getMessage() . "\n");
}

// Step 2: Copy data from luyenhao_CinemaBooking
echo "Step 2: Copying data from luyenhao_CinemaBooking...\n";

$tablesToCopy = [
    'users',
    'genres',
    'languages',
    'cast',
    'cities',
    'movies',
    'movie_genre',
    'movie_language',
    'movie_cast',
    'theaters',
    'screens',
    'seats',
    'showtimes',
    'bookings',
    'booking_seats',
    'seat_locks',
    'transactions',
    'reviews'
];

$copiedTables = [];
DB::statement("SET FOREIGN_KEY_CHECKS = 0");

foreach ($tablesToCopy as $table) {
    try {
        // Check if table exists in source
        $sourceExists = DB::select("SELECT COUNT(*) as count FROM information_schema.tables 
                                    WHERE table_schema = 'luyenhao_CinemaBooking' 
                                    AND table_name = '$table'")[0]->count;

        // Check if table exists in target
        $targetExists = DB::select("SELECT COUNT(*) as count FROM information_schema.tables 
                                    WHERE table_schema = '$currentDb' 
                                    AND table_name = '$table'")[0]->count;

        if ($sourceExists && $targetExists) {
            DB::statement("TRUNCATE TABLE `$currentDb`.`$table`");
            DB::statement("INSERT INTO `$currentDb`.`$table` SELECT * FROM `luyenhao_CinemaBooking`.`$table`");
            $count = DB::table($table)->count();
            echo "  ✓ $table: $count rows copied\n";
            $copiedTables[] = $table;
        }
    } catch (Exception $e) {
        echo "  ⚠ $table: " . substr($e->getMessage(), 0, 50) . "...\n";
    }
}

DB::statement("SET FOREIGN_KEY_CHECKS = 1");
echo "\n✅ Data copy complete! Copied " . count($copiedTables) . " tables.\n\n";

// Step 3: Fix VIP Seat Pricing
echo "Step 3: Fixing VIP seat pricing...\n";

try {
    // Check if extra_price column exists
    $columns = DB::select("SHOW COLUMNS FROM seats LIKE 'extra_price'");

    if (empty($columns)) {
        echo "  📝 Adding 'extra_price' column...\n";
        DB::statement("ALTER TABLE seats ADD COLUMN extra_price DECIMAL(10,0) DEFAULT 0 AFTER seat_type");
        echo "  ✅ Column added!\n";
    } else {
        echo "  ✓ Column 'extra_price' already exists\n";
    }

    // Update seat pricing based on type
    echo "  💰 Updating seat prices...\n";

    $vipCount = DB::statement("UPDATE seats SET extra_price = 10000 WHERE seat_type = 'vip' OR type = 'vip'");
    echo "    • VIP seats: extra_price = 10,000 VND\n";

    $coupleCount = DB::statement("UPDATE seats SET extra_price = 20000 WHERE seat_type = 'couple' OR type = 'couple'");
    echo "    • Couple seats: extra_price = 20,000 VND\n";

    $standardCount = DB::statement("UPDATE seats SET extra_price = 0 WHERE seat_type = 'standard' OR type = 'standard'");
    echo "    • Standard seats: extra_price = 0 VND\n";

    echo "  ✅ Pricing updated!\n\n";

} catch (Exception $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n\n";
}

// Step 4: Verification
echo "Step 4: Verifying data...\n";
echo "┌─────────────────────┬──────────┐\n";
echo "│ Table               │ Rows     │\n";
echo "├─────────────────────┼──────────┤\n";

$verifyTables = ['users', 'movies', 'genres', 'cities', 'theaters', 'screens', 'seats', 'showtimes', 'bookings'];
foreach ($verifyTables as $table) {
    try {
        $count = DB::table($table)->count();
        echo sprintf("│ %-19s │ %8d │\n", $table, $count);
    } catch (Exception $e) {
        echo sprintf("│ %-19s │ %8s │\n", $table, "ERROR");
    }
}
echo "└─────────────────────┴──────────┘\n\n";

// Step 5: Check VIP seat pricing
echo "Step 5: Checking VIP seat pricing...\n";
try {
    $seatPricing = DB::select("
        SELECT 
            COALESCE(seat_type, type) as seat_type,
            extra_price,
            COUNT(*) as count
        FROM seats
        GROUP BY COALESCE(seat_type, type), extra_price
        ORDER BY seat_type, extra_price
    ");

    echo "┌──────────────┬──────────────┬───────┐\n";
    echo "│ Seat Type    │ Extra Price  │ Count │\n";
    echo "├──────────────┼──────────────┼───────┤\n";
    foreach ($seatPricing as $row) {
        echo sprintf("│ %-12s │ %12s │ %5d │\n", $row->seat_type, number_format($row->extra_price), $row->count);
    }
    echo "└──────────────┴──────────────┴───────┘\n\n";

    // Final pricing summary
    echo "💵 Final Pricing (with base_price = 90,000 VND):\n";
    echo "  • VIP Seats:      90,000 + 10,000 = 100,000 VND ✓\n";
    echo "  • Couple Seats:   90,000 + 20,000 = 110,000 VND ✓\n";
    echo "  • Standard Seats: 90,000 + 0     = 90,000 VND ✓\n\n";

} catch (Exception $e) {
    echo "❌ ERROR checking pricing: " . $e->getMessage() . "\n\n";
}

echo "==========================================================\n";
echo "✅ DATABASE SETUP COMPLETE!\n";
echo "==========================================================\n\n";

echo "Next steps:\n";
echo "1. Test frontend: http://localhost:3000\n";
echo "2. Go to Booking page\n";
echo "3. Select seats and verify pricing\n\n";

echo "If you encounter issues, check:\n";
echo "  • Laravel server running: php artisan serve\n";
echo "  • Frontend running: npm start\n";
echo "  • Database connection in .env file\n";
