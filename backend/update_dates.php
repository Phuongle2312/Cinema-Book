<?php

use App\Models\Showtime;
use Carbon\Carbon;

$today = '2026-01-08';
echo "Updating showtimes to $today...\n";

$showtimes = Showtime::all();
foreach ($showtimes as $s) {
    echo 'Processing ID: '.$s->showtime_id.' | Time: '.$s->show_time."\n";

    // Parse liberally
    $originalTime = Carbon::parse($s->show_time);

    $s->show_date = $today;
    $s->start_time = Carbon::parse($today.' '.$originalTime->format('H:i:s'));

    $s->save();
}

echo 'Updated '.$showtimes->count()." showtimes.\n";

// Verify
$count = Showtime::with(['room.theater.city'])
    ->whereDate('start_time', $today)
    ->whereHas('room.theater.city', function ($q) {
        $q->where('name', 'LIKE', '%Ho Chi Minh%');
    })
    ->count();

echo 'Verified Showtimes for HCM Today: '.$count."\n";
