<?php
use App\Models\City;
use App\Models\Theater;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\Movie;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

echo "--- FORCE POPULATE DATA ---\n";
Model::unguard(); 

$targetCities = [
    'Hà Nội', 'Đà Nẵng', 'Cần Thơ', 'Đồng Nai', 'Hải Phòng', 'Quảng Ninh'
];

$today = '2026-01-08';
$movies = Movie::all();
$movie = $movies->first();
$times = ['10:00', '14:00', '19:00', '21:30'];

function getNextId2($model, $key) {
    return $model::max($key) + 1;
}

foreach ($targetCities as $cityName) {
    echo "Processing $cityName...\n";
    
    // 1. City
    $city = City::where('name', $cityName)->first();
    if (!$city) {
        $id = getNextId2(City::class, 'city_id');
        $city = City::create([
            'city_id' => $id,
            'name' => $cityName,
            'slug' => Str::slug($cityName),
            'country' => 'Vietnam',
            'timezone' => 'Asia/Ho_Chi_Minh'
        ]);
        echo "Created City: $cityName\n";
    }

    // 2. Theater
    $theaterName = "CGV Vincom $cityName";
    $theater = Theater::where('name', $theaterName)->where('city_id', $city->city_id)->first();
    if (!$theater) {
        $id = getNextId2(Theater::class, 'theater_id');
        $theater = Theater::create([
            'theater_id' => $id,
            'city_id' => $city->city_id, 
            'name' => $theaterName,
            'slug' => Str::slug($theaterName),
            'address' => "Vincom Center, $cityName",
            'phone' => '1900 1234',
            'is_active' => 1
        ]);
        echo "Created Theater: $theaterName\n";
    }

    // 3. Room (Always check by Theater ID)
    $roomName = 'Room 1';
    $room = Room::where('theater_id', $theater->theater_id)->where('name', $roomName)->first();
    if (!$room) {
        $id = getNextId2(Room::class, 'room_id');
        $room = Room::create([
            'room_id' => $id,
            'theater_id' => $theater->theater_id, 
            'name' => $roomName,
            'total_seats' => 50,
            'screen_type' => 'standard'
        ]);
        echo "Created Room: $roomName for $cityName\n";
    }

    // 4. Showtimes
    foreach ($times as $time) {
        $start = Carbon::parse("$today $time");
        
        $exists = Showtime::where('room_id', $room->room_id)
            ->where('start_time', $start)
            ->exists();
            
        if (!$exists) {
            $id = getNextId2(Showtime::class, 'showtime_id');
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
                'available_seats' => 50
            ]);
            echo "Added Showtime: $time\n";
        }
    }
}
echo "Populate Complete.\n";
