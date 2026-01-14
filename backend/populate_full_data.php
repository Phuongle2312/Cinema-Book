<?php

use App\Models\City;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\Theater;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

echo "--- POPULATING FULL DATA (MANUAL ID + UNGUARD) ---\n";
Model::unguard(); // Allow setting IDs manually

$targetCities = [
    'Hà Nội', 'Đà Nẵng', 'Cần Thơ', 'Đồng Nai', 'Hải Phòng', 'Quảng Ninh',
];

$today = '2026-01-08';
$movies = Movie::all();

if ($movies->isEmpty()) {
    exit("No movies found! Run seeders or check database.\n");
}

$movie = $movies->first();
$times = ['10:00', '14:00', '19:00', '21:30'];

// Helpers for ID
function getNextId($model, $key)
{
    return $model::max($key) + 1;
}

foreach ($targetCities as $cityName) {
    echo "Processing City: $cityName\n";

    // 1. City
    $city = City::where('name', $cityName)->first();
    if (! $city) {
        $id = getNextId(City::class, 'city_id');
        $city = City::create([
            'city_id' => $id,
            'name' => $cityName,
            'slug' => Str::slug($cityName),
            'country' => 'Vietnam',
            'timezone' => 'Asia/Ho_Chi_Minh',
        ]);
        echo " - Created City [$id] $cityName\n";
    }

    // 2. Theater
    $theaterName = "CGV Vincom $cityName";
    $theater = Theater::where('name', $theaterName)->where('city_id', $city->city_id)->first();
    if (! $theater) {
        $id = getNextId(Theater::class, 'theater_id');
        $theater = Theater::create([
            'theater_id' => $id,
            'city_id' => $city->city_id,
            'name' => $theaterName,
            'slug' => Str::slug($theaterName),
            'address' => "Vincom Center, $cityName",
            'phone' => '1900 1234',
            'is_active' => 1,
        ]);
        echo " - Created Theater [$id] $theaterName\n";
    }

    // 3. Room
    $roomName = 'Room 1';
    $room = Room::where('theater_id', $theater->theater_id)->where('name', $roomName)->first();
    if (! $room) {
        $id = getNextId(Room::class, 'room_id');
        $room = Room::create([
            'room_id' => $id,
            'theater_id' => $theater->theater_id,
            'name' => $roomName,
            'total_seats' => 50,
            'screen_type' => 'standard',
        ]);
        echo " - Created Room [$id] $roomName\n";
    }

    // 4. Showtimes
    foreach ($times as $time) {
        $start = Carbon::parse("$today $time");

        $exists = Showtime::where('room_id', $room->room_id)
            ->where('start_time', $start)
            ->exists();

        if (! $exists) {
            $id = getNextId(Showtime::class, 'showtime_id');
            Showtime::create([
                'showtime_id' => $id,
                'movie_id' => $movie->movie_id,
                'room_id' => $room->room_id,
                'show_date' => $today,
                'show_time' => $time,
                'start_time' => $start,
                'base_price' => 100000,
                'vip_price' => 120000,
                'is_active' => 1,
                'status' => 'scheduled',
                'available_seats' => 50,
            ]);
            echo " - Created Showtime [$id] at $time\n";
        }
    }
}

echo "Done.\n";
