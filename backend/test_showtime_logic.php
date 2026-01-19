<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\City;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\Theater;
use App\Models\User;
use App\Models\Booking;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

ob_start();
echo "--- STARTING SHOWTIME & BOOKING LOGIC AUDIT ---\n";

DB::beginTransaction();

try {
    // 1. SETUP
    $user = User::create(['name' => 'Time User', 'email' => 'timeuser_' . time() . '@test.com', 'password' => Hash::make('password')]);

    $city = City::firstOrCreate(['slug' => 'audit-city'], ['name' => 'Audit City']);
    $theater = Theater::firstOrCreate(['slug' => 'audit-theater'], ['name' => 'Audit Theater', 'city_id' => $city->city_id, 'address' => '123 Test St']);
    $room = Room::create(['name' => 'Time Room', 'theater_id' => $theater->theater_id, 'screen_type' => 'Standard']);

    $movie = Movie::create(['title' => 'Time Movie', 'slug' => 'time-movie', 'duration' => 120, 'release_date' => now(), 'status' => 'now_showing']);

    // ==========================================
    // TEST 1: SHOWTIME CONFLICT (ADMIN)
    // ==========================================
    echo "\n[Test 1] Showtime Conflict Check (Overlapping times)\n";

    // Create first showtime: Today 10:00 -> 12:00 (+15m cleanup) = 12:15
    $st1 = Showtime::create([
        'movie_id' => $movie->movie_id,
        'room_id' => $room->room_id,
        'start_time' => Carbon::today()->addHours(10),
        'base_price' => 100000
    ]);
    echo "  -> Created ST1: " . $st1->start_time->format('H:i') . " to " . $st1->end_time->addMinutes(15)->format('H:i') . " (incl cleanup)\n";

    // Try creating overlapping showtime: Today 11:00 (Inside ST1)
    // We'll simulate Controller's checkOverlap method logic here since it's private/protected in controller.
    // Ideally we instantiate controller but that requires Request mocking. 
    // We will reproduce the logic found in ShowtimeController::checkOverlap manually to verify the ALGORITHM.

    $newStart = Carbon::today()->addHours(11);
    $duration = 120;
    $cleaningTime = 15;
    $newEnd = $newStart->copy()->addMinutes($duration + $cleaningTime);

    $overlap = Showtime::where('room_id', $room->room_id)
        ->where('showtime_id', '!=', 99999) // exclude self
        ->where(function ($q) use ($newStart, $newEnd, $cleaningTime) {
            // Logic from Controller:
            // if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart))
            // We need to fetch and loop because 'end_time' isn't a column anymore (dynamic).
            // OR we rely on assumption that `checkOverlap` in controller does a loop.
            // YES, the controller loops. So we loop too.
        })->get();

    $isConflict = false;
    foreach ($overlap as $existing) {
        $existingStart = Carbon::parse($existing->start_time);
        $existingDuration = $existing->movie ? $existing->movie->duration : 0;
        $existingEnd = $existingStart->copy()->addMinutes($existingDuration + $cleaningTime);

        if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
            $isConflict = true;
            echo "  -> [PASS] Conflict Detected! Overlap with ST " . $existing->showtime_id . "\n";
            break;
        }
    }

    if (!$isConflict) {
        echo "  -> [FAIL] Logic failed to detect overlap at 11:00\n";
    }

    // ==========================================
    // TEST 2: BOOKING PAST SHOWTIME (USER)
    // ==========================================
    echo "\n[Test 2] User tries to book PAST showtime (Real-time check)\n";

    // Create a Past Showtime (Yesterday)
    $pastShowtime = Showtime::create([
        'movie_id' => $movie->movie_id,
        'room_id' => $room->room_id,
        'start_time' => Carbon::now()->subDay(), // 24 hours ago
        'base_price' => 100000
    ]);

    $seat = Seat::create(['room_id' => $room->room_id, 'row' => 'P', 'number' => 1, 'type' => 'standard', 'seat_code' => 'P1']);

    $service = app(BookingService::class);

    try {
        // Attempt to create booking
        $service->createBooking($user, $pastShowtime, [$seat->seat_id]);
        echo "  -> [FAIL] System ALLOWED booking a past showtime! (Critical)\n";
    } catch (\Exception $e) {
        // We expect an exception or validation error about "Showtime has started/ended"
        echo "  -> [PASS? or FAIL?] Exception: " . $e->getMessage() . "\n";
        // If message is generic, we need to IMPROVE it.
        // Current Code likely DOES NOT check time.
    }

    // ==========================================
    // TEST 3: BOOKING FUTURE SHOWTIME
    // ==========================================
    echo "\n[Test 3] User tries to book FUTURE showtime\n";
    $futureShowtime = Showtime::create([
        'movie_id' => $movie->movie_id,
        'room_id' => $room->room_id,
        'start_time' => Carbon::now()->addDays(5),
        'base_price' => 100000
    ]);

    try {
        $service->createBooking($user, $futureShowtime, [$seat->seat_id]); // Re-use seat ID (diff showtime)
        echo "  -> [PASS] Future booking succeeded.\n";
    } catch (\Exception $e) {
        echo "  -> [FAIL] Future booking failed: " . $e->getMessage() . "\n";
    }

} catch (\Exception $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n" . $e->getTraceAsString();
} finally {
    DB::rollBack();
    echo "\n--- FINISHED (Rolled back DB) ---\n";

    $output = ob_get_clean();
    file_put_contents(__DIR__ . '/audit_time_logic.log', $output);
}
