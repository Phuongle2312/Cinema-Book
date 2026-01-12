<?php
use App\Models\City;
use App\Models\Theater;
use App\Models\Room;
use App\Models\Showtime;

$city = City::where('name', 'LIKE', '%Ho Chi Minh%')->first();
if (!$city) die("HCM not found");

$tIds = Theater::where('city_id', $city->city_id)->pluck('theater_id');
$rIds = Room::whereIn('theater_id', $tIds)->pluck('room_id');

$showtimes = Showtime::whereIn('room_id', $rIds)
    ->whereDate('start_time', '2026-01-08') // Today
    ->get()
    ->unique('show_time');

echo "HCM Showtimes:\n";
foreach ($showtimes as $s) {
    echo $s->show_time . "\n";
}
