<?php

use App\Models\City;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\Theater;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

echo "--- SYNC SHOWTIMES FROM HCM ---\n";
Model::unguard();

// 1. Get Source Times from HCM
$hcm = City::where('name', 'LIKE', '%Ho Chi Minh%')->first();
$hcmTIds = Theater::where('city_id', $hcm->city_id)->pluck('theater_id');
$hcmRIds = Room::whereIn('theater_id', $hcmTIds)->pluck('room_id');

$sourceShowtimes = Showtime::whereIn('room_id', $hcmRIds)
    ->whereDate('start_time', '2026-01-08')
    ->get()
    ->unique('show_time'); // Get unique slots: 10:00, 14:00, 18:00, 21:00

$times = $sourceShowtimes->pluck('show_time')->toArray();
$movieId = $sourceShowtimes->first()->movie_id;

echo 'Source Times (HCM): '.implode(', ', $times)."\n";

// 2. Target Cities
$targetCities = ['Hà Nội', 'Đà Nẵng', 'Cần Thơ', 'Đồng Nai', 'Hải Phòng', 'Quảng Ninh'];
$today = '2026-01-08';

function getNextId3($model, $key)
{
    return $model::max($key) + 1;
}

foreach ($targetCities as $cityName) {
    echo "Syncing $cityName...\n";
    $city = City::where('name', $cityName)->first();
    if (! $city) {
        echo " - City not found, skipping.\n";

        continue;
    }

    $theaters = Theater::where('city_id', $city->city_id)->get();
    foreach ($theaters as $t) {
        $room = Room::where('theater_id', $t->theater_id)->first();
        if (! $room) {
            echo " - No room in $t->name, skipping.\n";

            continue;
        }

        foreach ($times as $time) {
            $start = Carbon::parse("$today $time");

            // Check if exists to avoid duplicates
            // We use loose checking on time to allow small diffs, or exact match
            $exists = Showtime::where('room_id', $room->room_id)
                ->where('start_time', $start)
                ->exists();

            if (! $exists) {
                $id = getNextId3(Showtime::class, 'showtime_id');
                Showtime::create([
                    'showtime_id' => $id,
                    'movie_id' => $movieId,
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
                echo "   + Added $time\n";
            } else {
                // echo "   . Skipped $time (Exists)\n";
            }
        }
    }
}
echo "Sync Complete.\n";
