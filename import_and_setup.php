<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================================\n";
echo "   CINEMA BOOK - SQL FILE IMPORT & SETUP\n";
echo "==========================================================\n\n";

$sqlFile = __DIR__ . '/luyenhao_CinemaBooking.sql';

if (!file_exists($sqlFile)) {
    die("❌ ERROR: SQL file not found at: $sqlFile\n");
}

echo "📁 Found SQL file: luyenhao_CinemaBooking.sql\n";
echo "📊 Target database: " . DB::connection()->getDatabaseName() . "\n\n";

// Step 1: Read and prepare SQL file
echo "Step 1: Reading SQL file...\n";
$sql = file_get_contents($sqlFile);

// Handle encoding
if (mb_detect_encoding($sql, 'UTF-16LE', true)) {
    $sql = mb_convert_encoding($sql, 'UTF-8', 'UTF-16LE');
}
$sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql); // Remove BOM

// Split into statements
$statements = array_filter(
    array_map('trim', preg_split('/;[\r\n]+/', $sql)),
    function ($stmt) {
        return !empty($stmt) &&
            !preg_match('/^(--|\/\*|#)/', $stmt) &&
            !preg_match('/^(USE |SET |CREATE DATABASE)/i', $stmt);
    }
);

echo "✅ Found " . count($statements) . " SQL statements\n\n";

// Step 2: Execute SQL statements
echo "Step 2: Importing data...\n";
DB::statement("SET FOREIGN_KEY_CHECKS = 0");

$success = 0;
$errors = 0;
$progress = 0;

foreach ($statements as $index => $statement) {
    try {
        DB::statement($statement);
        $success++;

        $progress++;
        if ($progress % 50 == 0) {
            echo "  ✓ Processed $progress statements...\n";
        }
    } catch (Exception $e) {
        $errors++;
        if ($errors <= 5) { // Only show first 5 errors
            echo "  ⚠ Error on statement " . ($index + 1) . ": " . substr($e->getMessage(), 0, 60) . "...\n";
        }
    }
}

DB::statement("SET FOREIGN_KEY_CHECKS = 1");

echo "\n✅ Import complete!\n";
echo "  • Success: $success statements\n";
echo "  • Errors: $errors statements\n\n";

// Step 3: Fix VIP Seat Pricing
echo "Step 3: Fixing VIP seat pricing...\n";

try {
    // Check if extra_price column exists
    $columns = DB::select("SHOW COLUMNS FROM seats");
    $hasExtraPrice = false;
    $typeColumn = null;

    foreach ($columns as $col) {
        if ($col->Field === 'extra_price') {
            $hasExtraPrice = true;
        }
        if (in_array($col->Field, ['seat_type', 'type'])) {
            $typeColumn = $col->Field;
        }
    }

    if (!$typeColumn) {
        echo "  ❌ ERROR: No seat type column found (seat_type or type)\n\n";
    } else {
        echo "  ✓ Using column: $typeColumn\n";

        if (!$hasExtraPrice) {
            echo "  📝 Adding 'extra_price' column...\n";
            DB::statement("ALTER TABLE seats ADD COLUMN extra_price DECIMAL(10,0) DEFAULT 0");
            echo "  ✅ Column added!\n";
        }

        // Update prices
        echo "  💰 Updating seat prices...\n";

        $vip = DB::table('seats')->where($typeColumn, 'vip')->update(['extra_price' => 10000]);
        echo "    • VIP seats: $vip updated (10,000 VND)\n";

        $couple = DB::table('seats')->where($typeColumn, 'couple')->update(['extra_price' => 20000]);
        echo "    • Couple seats: $couple updated (20,000 VND)\n";

        $standard = DB::table('seats')->where($typeColumn, 'standard')->update(['extra_price' => 0]);
        echo "    • Standard seats: $standard updated (0 VND)\n";

        echo "  ✅ Pricing fixed!\n\n";
    }

} catch (Exception $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n\n";
}

// Step 4: Verification
echo "Step 4: Verifying imported data...\n";
echo "┌─────────────────────┬──────────┐\n";
echo "│ Table               │ Rows     │\n";
echo "├─────────────────────┼──────────┤\n";

$tables = ['users', 'movies', 'genres', 'cities', 'theaters', 'screens', 'seats', 'showtimes', 'bookings'];
foreach ($tables as $table) {
    try {
        $count = DB::table($table)->count();
        echo sprintf("│ %-19s │ %8d │\n", $table, $count);
    } catch (Exception $e) {
        echo sprintf("│ %-19s │ %8s │\n", $table, "N/A");
    }
}
echo "└─────────────────────┴──────────┘\n\n";

// Step 5: Show seat pricing
echo "Step 5: Seat pricing summary...\n";
try {
    if ($typeColumn) {
        $pricing = DB::select("
            SELECT $typeColumn as type, extra_price, COUNT(*) as count
            FROM seats
            GROUP BY $typeColumn, extra_price
            ORDER BY $typeColumn
        ");

        echo "┌──────────────┬──────────────┬───────┐\n";
        echo "│ Type         │ Extra Price  │ Count │\n";
        echo "├──────────────┼──────────────┼───────┤\n";
        foreach ($pricing as $row) {
            echo sprintf("│ %-12s │ %,12d │ %5d │\n", $row->type, $row->extra_price, $row->count);
        }
        echo "└──────────────┴──────────────┴───────┘\n\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
}

echo "==========================================================\n";
echo "✅ SETUP COMPLETE!\n";
echo "==========================================================\n\n";

echo "💵 Expected Pricing (base_price = 90,000 VND):\n";
echo "  • VIP Seats:      90,000 + 10,000 = 100,000 VND ✓\n";
echo "  • Couple Seats:   90,000 + 20,000 = 110,000 VND ✓\n";
echo "  • Standard Seats: 90,000 + 0     = 90,000 VND ✓\n\n";

echo "🎬 Next: Test on frontend at http://localhost:3000\n";
