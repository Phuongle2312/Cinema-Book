<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\City;
use App\Models\Combo;
use App\Models\Movie;
use App\Models\Offer;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\Theater;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class OfferFlowTest extends TestCase
{
    use DatabaseTransactions; // Wraps test in transaction, rolls back after. Safest for local DB.

    public function test_auto_offer_voucher_override_and_remove_flow()
    {
        // 1. SETUP DATA
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test_offer_flow_' . time() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'user'
        ]);

        // Movie & Showtime
        $movie = Movie::create([
            'title' => 'Test Movie',
            'slug' => 'test-movie',
            'duration' => 120,
            'release_date' => now(),
            'status' => 'now_showing'
        ]);

        $city = City::create(['name' => 'Test City', 'slug' => 'test-city']);
        $theater = Theater::create(['name' => 'Test Theater', 'city_id' => $city->city_id, 'slug' => 'test-theater']);
        $room = Room::create(['name' => 'Room 1', 'theater_id' => $theater->theater_id, 'screen_type' => 'Standard']);

        $showtime = Showtime::create([
            'movie_id' => $movie->movie_id,
            'room_id' => $room->room_id,
            'start_time' => now()->addDay(),
            'base_price' => 100000
        ]);

        $seat = Seat::create(['room_id' => $room->room_id, 'row' => 'A', 'number' => 1, 'type' => 'standard', 'extra_price' => 0, 'seat_code' => 'A1']);

        // 2. CREATE OFFERS

        // A. SYSTEM AUTO OFFER (10% Off)
        $autoOffer = Offer::create([
            'title' => 'Auto 10%',
            'is_system_wide' => true,
            'code' => null, // NO CODE
            'discount_type' => 'percentage',
            'discount_value' => 10, // 10%
            'valid_from' => now()->subDay(),
            'valid_to' => now()->addDay(),
            'is_active' => true,
        ]);

        // B. VOUCHER OFFER (50% Off)
        $voucherOffer = Offer::create([
            'title' => 'Voucher 50%',
            'is_system_wide' => false,
            'code' => 'VIP50', // HAS CODE
            'discount_type' => 'percentage',
            'discount_value' => 50, // 50%
            'valid_from' => now()->subDay(),
            'valid_to' => now()->addDay(),
            'is_active' => true,
        ]);

        // 3. STEP 1: CREATE BOOKING -> EXPECT AUTO OFFER
        $response = $this->actingAs($user)->postJson('/api/bookings', [
            'showtime_id' => $showtime->showtime_id,
            'seat_ids' => [$seat->seat_id]
        ]);

        $response->assertStatus(201);
        $bookingId = $response->json('data.booking_id');
        $totalPrice = $response->json('data.total_price');
        $discountAmount = $response->json('data.discount_amount');
        $offerId = $response->json('data.offer_id');

        // Base price 100k. Auto discount 10% = 10k. Total = 90k.
        $this->assertEquals(10000, $discountAmount, 'Auto offer discount verify');
        $this->assertEquals(90000, $totalPrice, 'Auto offer total verify');
        $this->assertEquals($autoOffer->id, $offerId, 'Auto offer ID verify'); // Note: id vs offer_id check

        echo "\n[PASS] Step 1: Booking created with Auto Offer (10%).\n";

        // 4. STEP 2: APPLY VOUCHER -> EXPECT OVERRIDE
        $response = $this->actingAs($user)->postJson("/api/bookings/{$bookingId}/apply-offer", [
            'offer_code' => 'VIP50'
        ]);

        $response->assertStatus(200);
        $totalPrice = $response->json('data.total_price');
        $discountAmount = $response->json('data.discount_amount');

        // Base 100k. Voucher 50% = 50k. Total = 50k.
        $this->assertEquals(50000, $discountAmount, 'Voucher discount verify');
        $this->assertEquals(50000, $totalPrice, 'Voucher total verify');

        echo "[PASS] Step 2: Voucher 'VIP50' applied, overriding Auto Offer.\n";

        // 5. STEP 3: REMOVE VOUCHER -> EXPECT AUTO RESTORE
        $response = $this->actingAs($user)->postJson("/api/bookings/{$bookingId}/remove-offer");

        $response->assertStatus(200);
        $totalPrice = $response->json('data.total_price');
        $discountAmount = $response->json('data.discount_amount');
        $offerId = $response->json('data.offer_id');

        // Should return to Auto Offer values: 10k discount, 90k total
        $this->assertEquals(10000, $discountAmount, 'Post-remove discount verify (should be auto)');
        $this->assertEquals(90000, $totalPrice, 'Post-remove total verify (should be auto)');
        $this->assertNotNull($offerId);

        echo "[PASS] Step 3: Voucher removed, Auto Offer restored successfully.\n";
    }
}
