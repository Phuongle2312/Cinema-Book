<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Movie;
use App\Models\Theater;
use App\Models\Room;
use App\Models\Showtime;
use Carbon\Carbon;

class MissingShowtimeSeeder extends Seeder
{
    public function run()
    {
        $movies = Movie::where('status', 'now_showing')->get();
        $theaters = Theater::with('rooms')->get();

        if ($theaters->isEmpty()) {
            $this->command->error('No theaters found!');
            return;
        }

        foreach ($movies as $movie) {
            $futureShowtimesCount = Showtime::where('movie_id', $movie->movie_id)
                ->where('start_time', '>', now())
                ->count();

            if ($futureShowtimesCount > 5) {
                // $this->command->info("Skipping {$movie->title} - has {$futureShowtimesCount} showtimes.");
                continue;
            }

            $this->command->info("Seeding showtimes for: {$movie->title}");

            for ($day = 0; $day < 5; $day++) {
                $date = Carbon::today()->addDays($day);

                foreach ($theaters as $theater) {
                    if ($theater->rooms->isEmpty())
                        continue;

                    $rooms = $theater->rooms->random(min(2, $theater->rooms->count()));

                    foreach ($rooms as $room) {
                        $slots = [10, 14, 19, 21];
                        $chosenSlots = array_rand(array_flip($slots), 2);

                        foreach ($chosenSlots as $hour) {
                            $startTime = $date->copy()->setTime($hour, 0, 0);
                            $endTime = $startTime->copy()->addMinutes($movie->duration + 20);

                            // RAW AND ROBUST overlap check
                            // start_time < NewEnd AND end_time > NewStart
                            $exists = Showtime::where('room_id', $room->id)
                                ->whereRaw('start_time < ? AND end_time > ?', [
                                    $endTime->format('Y-m-d H:i:s'),
                                    $startTime->format('Y-m-d H:i:s')
                                ])
                                ->exists();

                            if (!$exists) {
                                Showtime::create([
                                    'movie_id' => $movie->movie_id,
                                    'room_id' => $room->id,
                                    'start_time' => $startTime,
                                    'end_time' => $endTime,
                                    'base_price' => 75000,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // SPECIAL CASE: "Detective Conan" (Coming Soon)
        $conan = Movie::where('title', 'like', '%Conan%')->first();
        if ($conan && $conan->status === 'coming_soon') {
            $this->command->info("Note: Detective Conan is 'coming_soon'. Adding SNEAK PREVIEW showtimes for TOMORROW.");

            $tomorrow = Carbon::tomorrow();

            foreach ($theaters as $theater) {
                if ($theater->rooms->isNotEmpty()) {
                    $room = $theater->rooms->first();

                    if (Showtime::where('movie_id', $conan->movie_id)->whereDate('start_time', $tomorrow)->exists()) {
                        continue;
                    }

                    Showtime::create([
                        'movie_id' => $conan->movie_id,
                        'room_id' => $room->id,
                        'start_time' => $tomorrow->copy()->setTime(19, 0),
                        'end_time' => $tomorrow->copy()->setTime(19, 0)->addMinutes($conan->duration + 20),
                        'base_price' => 100000,
                    ]);
                }
            }
        }
        $this->command->info("Done seeding showtimes.");
    }
}
