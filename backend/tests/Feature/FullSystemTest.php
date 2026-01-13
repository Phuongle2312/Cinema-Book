<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Movie;
use App\Models\Showtime;
use App\Models\Seat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class FullSystemTest extends TestCase
{
    // We don't use RefreshDatabase here to test against the actual seeded DB data 
    // or we can use it and seed. For safety/idempotency, let's use it and seed.
    // use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Run seeders to ensure we have data (Movies, Theaters, Showtimes)
        // $this->seed();
    }

    /**
     * Test Public Data Endpoints (Homepage, Movies, Showtimes)
     */
    public function test_public_endpoints_are_accessible()
    {
        // 1. Homepage Movies (Featured)
        $response = $this->getJson('/api/movies/featured');
        $response->assertStatus(200);

        // 2. All Movies
        $response = $this->getJson('/api/movies');
        $response->assertStatus(200);

        // 3. Single Movie Details
        $movie = Movie::first();
        if ($movie) {
            $response = $this->getJson("/api/movies/{$movie->movie_id}");
            $response->assertStatus(200)
                     ->assertJsonPath('movie_id', $movie->movie_id);
        }

        // 4. Showtimes
        $response = $this->getJson('/api/showtimes');
        $response->assertStatus(200);
    }

    /**
     * Test Authentication Flow (Register, Login, Profile)
     */
    public function test_authentication_flow()
    {
        $email = 'testuser_' . uniqid() . '@example.com';
        $password = 'Password123!';

        // 1. Register
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'phone' => '0901234567'
        ]);
        $response->assertStatus(201); // Created

        // 2. Login
        $loginResponse = $this->postJson('/api/login', [
            'email' => $email,
            'password' => $password
        ]);
        $loginResponse->assertStatus(200)
                      ->assertJsonStructure(['access_token']);

        $token = $loginResponse->json('access_token');

        // 3. Get Profile
        $profileResponse = $this->withToken($token)->getJson('/api/user/profile');
        $profileResponse->assertStatus(200)
                        ->assertJsonPath('email', $email);
        
        return $token;
    }

    /**
     * Test Booking Flow (Showtime -> Seat -> Hold -> Book)
     */
    public function test_booking_flow()
    {
        // Setup User
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // 1. Find a valid Showtime
        $showtime = Showtime::with(['room.seats'])->where('status', 'scheduled')->first();
        $this->assertNotNull($showtime, 'No scheduled showtime found.');

        // 2. Get Seats for Showtime
        $response = $this->getJson("/api/showtimes/{$showtime->showtime_id}/seats");
        $response->assertStatus(200);

        // 3. Pick a specific seat to book (First available seat)
        // Adjust logic based on how seats are returned. Usually they are linked to Room.
        // Assuming seat_code or seat_id is needed.
        // Let's grab a seat from the room that is NOT booked.
        
        $seat = Seat::where('room_id', $showtime->room_id)->first();
        $this->assertNotNull($seat, 'No seats found in room.');

        // 4. Create Booking (Pending + Hold)
        // Payload typically includes showtime_id and seat_ids again, and maybe payment info simulation
        $bookingPayload = [
            'showtime_id' => $showtime->showtime_id,
            'seat_ids' => [$seat->seat_id],
            'payment_method' => 'vnpay' // or 'local' check validation
        ];

        $bookingResponse = $this->withToken($token)->postJson('/api/bookings', $bookingPayload);
        
        // If booking is created successfully (Pending or Confirmed)
        $bookingResponse->assertSuccessful();
        
        // Optionally verify booking exists in DB
        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'showtime_id' => $showtime->showtime_id
        ]);
    }
}
