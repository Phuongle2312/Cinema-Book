<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Movie;
use App\Models\Theater;
use App\Models\Room;
use App\Models\Showtime;
use Carbon\Carbon;

class FullScheduleSeeder extends Seeder
{
    public function run()
    {
        // 0. Cleanup
        Schema::disableForeignKeyConstraints();
        DB::table('booking_combos')->truncate();
        DB::table('booking_seats')->truncate();
        DB::table('bookings')->truncate();
        DB::table('seat_locks')->truncate();
        DB::table('showtimes')->truncate();
        Schema::enableForeignKeyConstraints();

        $startDate = Carbon::today();
        $daysToSeed = 7;
        $theaters = Theater::with('rooms')->get();

        // 1. Seed Coming Soon (Sneak Previews) - PRIORITY
        $comingSoonMovies = Movie::where('status', 'coming_soon')->get();

        foreach ($comingSoonMovies as $movie) {
            $this->command->info("Scheduling Sneak Previews: {$movie->title}");

            for ($i = 0; $i < $daysToSeed; $i++) {
                $date = $startDate->copy()->addDays($i);

                foreach ($theaters as $theater) {
                    if ($theater->rooms->isEmpty())
                        continue;

                    $room = $theater->rooms->random();
                    $hour = rand(19, 20);

                    // Use ->room_id expicitly if ->id fails
                    $roomId = $room->room_id ?? $room->id;
                    $movieId = $movie->movie_id ?? $movie->id;

                    $this->createShowtime($movieId, $roomId, $date, $hour, 100000);
                }
            }
        }

        // 2. Seed Now Showing - Fill the rest
        $nowShowingMovies = Movie::where('status', 'now_showing')->get();
        $baseSlots = [10, 13, 16, 19, 21, 23];

        foreach ($nowShowingMovies as $movie) {
            $this->command->info("Scheduling Now Showing: {$movie->title}");

            for ($i = 0; $i < $daysToSeed; $i++) {
                $date = $startDate->copy()->addDays($i);

                foreach ($theaters as $theater) {
                    foreach ($theater->rooms as $room) {
                        $dailySlots = collect($baseSlots)->shuffle()->take(rand(3, 5));

                        $roomId = $room->room_id ?? $room->id;
                        $movieId = $movie->movie_id ?? $movie->id;

                        foreach ($dailySlots as $hour) {
                            $this->createShowtime($movieId, $roomId, $date, $hour, 85000);
                        }
                    }
                }
            }
        }
        $this->command->info("Full schedule generated successfully.");
    }

    private function createShowtime($movieId, $roomId, $date, $hour, $price)
    {
        $startTime = $date->copy()->setTime($hour, 0, 0);
        $startTime->addMinutes(rand(0, 15));

        $exists = Showtime::where('room_id', $roomId)
            ->whereBetween('start_time', [
                $startTime->copy()->subMinutes(120),
                $startTime->copy()->addMinutes(120)
            ])
            ->exists();

        if (!$exists) {
            try {
                Showtime::create([
                    'movie_id' => $movieId,
                    'room_id' => $roomId,
                    'start_time' => $startTime,
                    'base_price' => $price,
                ]);
            } catch (\Exception $e) {
                // Ignore overlaps
            }
        }
    }
}
