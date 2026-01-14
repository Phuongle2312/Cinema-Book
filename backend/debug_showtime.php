<?php

use App\Models\City;
use App\Models\Showtime;
use Carbon\Carbon;

echo "--- DEBUG INFO ---\n";
echo 'Today: '.Carbon::now()->toDateTimeString()."\n";

$hcm = City::where('name', 'LIKE', '%Ho Chi Minh%')->orWhere('name', 'LIKE', '%Hồ Chí Minh%')->first();
echo 'City ID found: '.($hcm ? $hcm->city_id : 'NULL')."\n";

$count = Showtime::whereDate('start_time', '2026-01-08')->count();
echo 'Total Showtimes on 2026-01-08: '.$count."\n";

$showtimes = Showtime::with(['room.theater.city'])
    ->whereDate('start_time', '2026-01-08')
    ->take(3)
    ->get();

foreach ($showtimes as $s) {
    echo 'Showtime ID: '.$s->showtime_id."\n";
    echo '  Start: '.$s->start_time."\n";
    echo '  Movie ID: '.$s->movie_id."\n";
    echo '  Room: '.($s->room ? $s->room->name : 'NULL').' (Type: '.($s->room ? $s->room->screen_type : 'NULL').")\n";
    echo '  Theater: '.($s->room && $s->room->theater ? $s->room->theater->name : 'NULL')."\n";
    echo '  City: '.($s->room && $s->room->theater && $s->room->theater->city ? $s->room->theater->city->name : 'NULL')."\n";
    echo '  Appended Format: '.$s->format."\n";
    echo "------------------\n";
}
