<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Showtime;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Transaction;
use App\Models\Seat;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class AdditionalBookingSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // 1. Ensure we have users
        if (User::count() < 20) {
            User::factory()->count(20)->create();
        }
        $userIds = User::pluck('id')->toArray();

        // 2. Get Random Showtimes (Past & Future)
        $showtimes = Showtime::with(['room.seats'])->inRandomOrder()->limit(20)->get();

        if ($showtimes->isEmpty()) {
            $this->command->info('No showtimes found. Skipping booking seeding.');
            return;
        }

        $count = 0;
        foreach ($showtimes as $showtime) {
            // Create 3-5 bookings per showtime
            $numBookings = rand(3, 5);

            for ($i = 0; $i < $numBookings; $i++) {
                $user = User::find($userIds[array_rand($userIds)]);

                // Pick random seats from the room
                $seats = $showtime->room->seats;
                if ($seats->isEmpty())
                    continue;

                // Pick 1-3 random seats
                $selectedSeats = $seats->random(rand(1, 3));

                // Calculate Total Price
                $totalPrice = 0;
                foreach ($selectedSeats as $seat) {
                    $totalPrice += $showtime->base_price + $seat->extra_price;
                }

                // Check if seats are already booked for this showtime
                // (Simple check: Just skip if any overlap, or assume our random seed is sparse enough)
                // For robustness, filtering would be better, but for dummy data, random is usually fine if dataset is large enough.
                // However, to avoid SQL errors:
                $conflicting = BookingSeat::where('showtime_id', $showtime->showtime_id)
                    ->whereIn('seat_id', $selectedSeats->pluck('seat_id'))
                    ->exists();

                if ($conflicting)
                    continue;

                DB::transaction(function () use ($showtime, $user, $selectedSeats, $totalPrice, $faker) {
                    // Create Booking
                    $booking = Booking::create([
                        'user_id' => $user->id,
                        'showtime_id' => $showtime->showtime_id,
                        'total_seats' => $selectedSeats->count(),
                        'total_price' => $totalPrice,
                        'status' => 'confirmed',
                        'booking_code' => strtoupper($faker->bothify('BK-????-####')),
                        'created_at' => $faker->dateTimeBetween('-1 month', 'now'),
                    ]);

                    // Create Booking Seats
                    foreach ($selectedSeats as $seat) {
                        BookingSeat::create([
                            'booking_id' => $booking->booking_id,
                            'seat_id' => $seat->seat_id,
                            'showtime_id' => $showtime->showtime_id,
                            'price' => $showtime->base_price + $seat->extra_price,
                        ]);
                    }

                    // Create Transaction
                    Transaction::create([
                        'booking_id' => $booking->booking_id,
                        'user_id' => $user->id,
                        'transaction_code' => strtoupper($faker->bothify('TRX-????-####')),
                        'amount' => $totalPrice,
                        'payment_method' => $faker->randomElement(['momo', 'vnpay', 'credit_card']),
                        'status' => 'success',
                        'paid_at' => now(),
                    ]);
                });
                $count++;
            }
        }

        $this->command->info("Seeded $count additional bookings successfully!");
    }
}
