<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Showtime;
use App\Models\Movie;
use App\Models\Theater;
use App\Models\Room;
use App\Models\Booking;
use App\Models\User;
use App\Models\Seat;
use Carbon\Carbon;

class ShowtimeAndPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create specific showtimes (if not exist)
        // Check prerequisite data
        $movie = Movie::first();
        if (!$movie) {
             $movie = Movie::create([
                'title' => 'Ma Trận: Hồi Sinh',
                'description' => 'Trở lại thế giới ảo...',
                'duration' => 148,
                'release_date' => '2021-12-22',
                'poster_url' => 'https://image.tmdb.org/t/p/w500/8c4a8kE7PizaGQQnditLsI1kUV8.jpg',
                'status' => 'now_showing',
                'rating' => 4.5
            ]);
        }
        
        $theater = Theater::with('rooms')->first();
        if (!$theater) {
             $city = \App\Models\City::firstOrCreate(['name' => 'Hồ Chí Minh'], ['slug' => 'hcm']);
             $theater = Theater::create([
                 'name' => 'CGV Vincom Đồng Khởi',
                 'city_id' => $city->id,
                 'address' => '72 Lê Thánh Tôn, Bến Nghé, Quận 1',
                 'total_rooms' => 5
             ]);
             // Create a room
             $theater->rooms()->create(['name' => 'Room 1', 'total_seats' => 50]);
             $theater = Theater::with('rooms')->find($theater->id);
        }
        
        if ($theater->rooms()->count() == 0) {
             $theater->rooms()->create(['name' => 'Room 1', 'total_seats' => 50]);
        }
        $room = $theater->rooms()->first();

        // Create showtimes for today and tomorrow
        $showtime1 = Showtime::firstOrCreate(
            [
                'room_id' => $room->getKey(),
                'start_time' => Carbon::today()->setHour(19)->setMinute(0),
            ],
            [
                'movie_id' => $movie->getKey(), 
                // 'theater_id' => $theater->id, // Removed
                // 'show_date' => Carbon::today(), // Removed
                // 'show_time' => '19:00:00', // Removed
                'base_price' => 85000,
                'status' => 'scheduled',
            ]
        );

        $showtime2 = Showtime::firstOrCreate(
            [
                'room_id' => $room->getKey(),
                'start_time' => Carbon::tomorrow()->setHour(20)->setMinute(0),
            ],
            [
                'movie_id' => $movie->getKey(), // Use getKey()
                // 'theater_id' => $theater->id, // Removed
                // 'show_date' => Carbon::today(), // Removed
                // 'show_time' => '19:00:00', // Removed
                'base_price' => 95000,
                'status' => 'scheduled',
            ]
        );
        
        // 2. Create pending payments
        // We need a user
        $user = User::first();
        if (!$user) {
             $user = User::create([
                'name' => 'Demo User',
                'email' => 'demo@user.com',
                'password' => bcrypt('password'),
                'role' => 'customer'
            ]);
        }

        // Create a booking
        $booking = Booking::create([
            'user_id' => $user->id,
            'showtime_id' => $showtime1->getKey(),
            'booking_code' => 'BK-' . uniqid(),
            'total_price' => 170000,
            'total_seats' => 2, // Added
            'status' => 'pending', // Payment pending
            // 'booking_date' => now(), // Removed
        ]);
        
        // Insert seats for booking
        // Need specific seats
        $seats = Seat::where('room_id', $room->id)->limit(2)->get();
        if ($seats->count() > 0) {
             foreach($seats as $seat) {
                 DB::table('booking_details')->insert([
                     'booking_id' => $booking->id,
                     'seat_id' => $seat->id,
                     'price' => 85000,
                     'created_at' => now(),
                     'updated_at' => now(),
                 ]);
             }
        }

        // Create PaymentVerification
        DB::table('payment_verifications')->insert([
            'booking_id' => $booking->getKey(), // Use getKey
            'user_id' => $user->id, // User id is standard
            'amount' => 170000,
            'payment_method' => 'Bank Transfer',
            // 'transaction_code' => 'TXN-' . rand(10000, 99999), // Removed
            // 'proof_image_url' => null, // or a mock url, Removed
            'status' => 'pending',
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info("Seeded Showtimes and Pending Payments.");
    }
}
