<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\City;
use App\Models\Movie;
use App\Models\Offer;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\Theater;
use App\Models\User;
use App\Services\BookingService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

ob_start();

echo "--- STARTING OFFER LOGIC AUDIT ---\n";

DB::beginTransaction();

try {
    // 1. SETUP
    $user = User::create([
        'name' => 'Audit User',
        'email' => 'audit_' . time() . '@test.com',
        'password' => Hash::make('password'),
        'role' => 'user'
    ]);

    $city = City::firstOrCreate(['slug' => 'audit-city'], ['name' => 'Audit City']);
    $theater = Theater::create([
        'name' => 'Audit Theater',
        'city_id' => $city->city_id,
        'slug' => 'audit-theater',
        'address' => '123 Audit Street' // REQUIRED FIELD
    ]);
    $room = Room::create(['name' => 'Audit Room', 'theater_id' => $theater->theater_id, 'screen_type' => 'Standard']);

    $movie = Movie::create([
        'title' => 'Audit Movie',
        'slug' => 'audit-movie',
        'duration' => 120,
        'release_date' => now(),
        'status' => 'now_showing'
    ]);

    $showtime = Showtime::create([
        'movie_id' => $movie->movie_id,
        'room_id' => $room->room_id,
        'start_time' => now()->addDay(),
        'base_price' => 100000 // 100k
    ]);

    $seat = Seat::create(['room_id' => $room->room_id, 'row' => 'X', 'number' => 1, 'type' => 'standard', 'extra_price' => 0, 'seat_code' => 'X1']);

    // 2. CREATE OFFERS
    // System Wide (Auto) - 10%
    $autoOffer = Offer::create([
        'title' => 'Auto 10%',
        'is_system_wide' => true,
        'code' => null,
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'valid_from' => now()->subDay(),
        'valid_to' => now()->addDay(),
        'is_active' => true,
    ]);

    // Voucher - 50%
    $voucherOffer = Offer::create([
        'title' => 'Voucher 50%',
        'is_system_wide' => false,
        'code' => 'AUDIT50',
        'discount_type' => 'percentage',
        'discount_value' => 50,
        'valid_from' => now()->subDay(),
        'valid_to' => now()->addDay(),
        'is_active' => true,
    ]);

    echo "[INFO] Data Created. Showtime Base: 100,000. Auto Offer: 10%. Voucher: 50%.\n";

    // 3. TEST 1: CREATE BOOKING (Expect Auto 10%)
    $service = app(BookingService::class);
    $booking = $service->createBooking($user, $showtime, [$seat->seat_id]);

    echo "\nTest 1: Auto Offer Application\n";
    echo "  - Total Price: " . number_format($booking->total_price) . "\n";
    echo "  - Discount Amount: " . number_format($booking->discount_amount) . "\n";
    echo "  - Offer ID: " . $booking->offer_id . "\n";

    if ($booking->discount_amount == 10000 && $booking->offer_id == $autoOffer->offer_id) {
        echo "  -> [PASS] Auto offer applied correctly (10k discount).\n";
    } else {
        echo "  -> [FAIL] Expected 10k discount from Auto Offer.\n";
    }

    // 4. TEST 2: APPLY VOUCHER (Expect Override to 50%)
    // Simulated Controller applyOffer Logic
    $subtotal = $booking->seats_total + $booking->combo_total; // 100k
    $discountAmount = $voucherOffer->calculateDiscount($subtotal); // 50k

    $booking->offer_id = $voucherOffer->offer_id;
    $booking->discount_amount = $discountAmount;
    $booking->total_price = max(0, $subtotal - $discountAmount);
    $booking->save();

    echo "\nTest 2: Voucher Override\n";
    echo "  - New Total Price: " . number_format($booking->total_price) . "\n";
    echo "  - New Discount: " . number_format($booking->discount_amount) . "\n";

    if ($booking->discount_amount == 50000) {
        echo "  -> [PASS] Voucher applied correctly (50k discount).\n";
    } else {
        echo "  -> [FAIL] Expected 50k discount from Voucher.\n";
    }

    // 5. TEST 3: REMOVE VOUCHER (Expect Auto Restore)
    // Simulated Controller removeOffer Logic
    $booking->offer_id = null;
    $booking->discount_amount = 0;

    $systemOffers = Offer::systemWide()->orderBy('discount_value', 'desc')->get();
    $bestOffer = null;
    $maxDiscount = 0;
    foreach ($systemOffers as $offer) {
        if ($offer->isValid()) {
            $discount = $offer->calculateDiscount($subtotal);
            if ($discount > $maxDiscount) {
                $maxDiscount = $discount;
                $bestOffer = $offer;
            }
        }
    }

    if ($bestOffer) {
        $booking->offer_id = $bestOffer->offer_id;
        $booking->discount_amount = $maxDiscount;
        $booking->total_price = max(0, $subtotal - $maxDiscount);
    } else {
        $booking->total_price = $subtotal;
    }
    $booking->save();

    echo "\nTest 3: Remove Voucher & Auto Restore\n";
    echo "  - Restored Total Price: " . number_format($booking->total_price) . "\n";
    echo "  - Restored Discount: " . number_format($booking->discount_amount) . "\n";

    if ($booking->discount_amount == 10000) {
        echo "  -> [PASS] Auto offer restored correctly (10k discount).\n";
    } else {
        echo "  -> [FAIL] Expected 10k discount restored.\n";
    }

} catch (\Exception $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
} finally {
    DB::rollBack();
    echo "\n--- FINISHED (Rolled back DB) ---\n";

    $output = ob_get_clean();
    file_put_contents(__DIR__ . '/audit_result.log', $output);
    echo "Log written to audit_result.log";
}
