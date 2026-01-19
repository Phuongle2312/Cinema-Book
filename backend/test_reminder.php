<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Models\Showtime;
use App\Models\User;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Theater;
use App\Models\City;
use App\Models\Seat;
use App\Models\BookingSeat;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

echo "--- STARTING REMINDER COMMAND AUDIT ---\n";

DB::beginTransaction();

try {
    Mail::fake(); // Don't actually send email, just mock

    // 1. SETUP DATA
    $user = null;
    try {
        $user = User::create(['name' => 'Reminder User', 'email' => 'remind_' . time() . '@test.com', 'password' => Hash::make('password')]);
        echo "User Created.\n";
    } catch (\Exception $e) {
        echo "User Failed: " . $e->getMessage() . "\n";
    }

    $city = City::firstOrCreate(['slug' => 'audit-remind-city'], ['name' => 'Audit Remind City']);

    $theaterId = null;
    try {
        $theaterId = DB::table('theaters')->insertGetId([
            'slug' => 'audit-remind-theater',
            'name' => 'Audit Remind Theater',
            'city_id' => $city->city_id,
            'address' => '123 Audit Remind St',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "Theater Created. ID: $theaterId\n";
    } catch (\Exception $e) {
        echo "Theater Failed: " . $e->getMessage() . "\n";
        $theater = DB::table('theaters')->where('slug', 'audit-remind-theater')->first();
        if ($theater)
            $theaterId = $theater->theater_id;
    }

    $roomId = null;
    if ($theaterId) {
        try {
            $roomId = DB::table('rooms')->insertGetId([
                'name' => 'Reminder Room',
                'theater_id' => $theaterId,
                'screen_type' => 'Standard',
                'total_seats' => 50,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "Room Created. ID: $roomId\n";
        } catch (\Exception $e) {
            echo "Room Failed: " . $e->getMessage() . "\n";
        }
    }

    $movieId = null;
    try {
        $movieId = DB::table('movies')->insertGetId([
            'title' => 'Reminder Movie',
            'slug' => 'remind-movie',
            'duration' => 120,
            'release_date' => now(),
            'status' => 'now_showing',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "Movie Created. ID: $movieId\n";
    } catch (\Exception $e) {
        echo "Movie Failed: " . $e->getMessage() . "\n";
        $m = DB::table('movies')->where('slug', 'remind-movie')->first();
        if ($m)
            $movieId = $m->movie_id;
    }

    $showtimeId = null;
    if ($movieId && $roomId) {
        try {
            $showtimeId = DB::table('showtimes')->insertGetId([
                'movie_id' => $movieId,
                'room_id' => $roomId,
                'start_time' => Carbon::now()->addHour(),
                'base_price' => 100000,
                'status' => 'scheduled',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "Showtime Created. ID: $showtimeId\n";
        } catch (\Exception $e) {
            echo "Showtime Failed: " . $e->getMessage() . "\n";
        }
    }

    $bookingId = null;
    if ($showtimeId && $user) {
        try {
            $bookingId = DB::table('bookings')->insertGetId([
                'user_id' => $user->id,
                'showtime_id' => $showtimeId,
                'status' => 'confirmed',
                'booking_code' => 'REMIND' . time(),
                'total_seats' => 1,
                'seats_total' => 100000, // Assuming this is total price for seats
                'total_price' => 100000,
                'reminder_sent_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "Booking Created. ID: $bookingId\n";
        } catch (\Exception $e) {
            echo "Booking Failed: " . $e->getMessage() . "\n";
        }
    }

    // SETUP SEATS FOR EMAIL RENDER
    $seat = new Seat();
    $seat->room_id = $roomId; // Use ID from DB insert
    $seat->row = 'A';
    $seat->number = 1;
    $seat->type = 'Standard';
    $seat->save();

    BookingSeat::create([
        'booking_id' => $bookingId,
        'seat_id' => $seat->seat_id,
        'showtime_id' => $showtimeId,
        'price' => 100000
    ]);

    // 2. RUN COMMAND
    if ($bookingId && $showtimeId) {
        $booking = Booking::find($bookingId);
        echo "\n[1] Running Command: app:send-booking-reminders\n";
        Artisan::call('app:send-booking-reminders');
        echo "Command Output: " . trim(Artisan::output()) . "\n";

        // 3. VERIFY
        // A. Check Mail Sent
        Mail::assertSent(\App\Mail\BookingReminderMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
        echo "[PASS] Email sent to " . $user->email . "\n";

        // B. Check DB Update
        $booking->refresh();
        if ($booking->reminder_sent_at) {
            echo "[PASS] Database updated. Reminder Sent At: " . $booking->reminder_sent_at . "\n";
        } else {
            echo "[FAIL] Database NOT updated!\n";
        }
    }

} catch (\Exception $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n" . $e->getTraceAsString();
} finally {
    DB::rollBack();
    echo "\n--- FINISHED (Rolled back DB) ---\n";
}
