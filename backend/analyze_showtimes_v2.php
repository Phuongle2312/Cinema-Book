<?php
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // 1. Get unique dates (simplified to avoid strict mode issues)
    $dates = DB::table('showtimes')
        ->select(DB::raw('DATE(start_time) as show_date'))
        ->distinct()
        ->orderBy('show_date')
        ->get();

    echo "Available Dates:\n";
    foreach ($dates as $d) {
        $count = DB::table('showtimes')->whereDate('start_time', $d->show_date)->count();
        echo $d->show_date . " (" . $count . " showtimes)\n";
    }

    // 2. Get unique cities
    // We need to know if it's room_id or screen_id
    // Based on previous step error output columns: "showtime_id, movie_id, room_id, start_time..." 
    // Wait, the previous output was truncated but I saw "room_id" in my previous turns? 
    // Actually, let's assume `room_id` because `rooms` table exists and `ShowtimeController` uses `room`.

    $cities = DB::table('showtimes')
        ->join('rooms', 'showtimes.room_id', '=', 'rooms.room_id')
        ->join('theaters', 'rooms.theater_id', '=', 'theaters.theater_id')
        ->join('cities', 'theaters.city_id', '=', 'cities.city_id')
        ->select('cities.name as city_name')
        ->distinct()
        ->get();

    echo "\nAvailable Cities for Showtimes:\n";
    foreach ($cities as $c) {
        // Count manually to be safe
        $count = DB::table('showtimes')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.room_id')
            ->join('theaters', 'rooms.theater_id', '=', 'theaters.theater_id')
            ->join('cities', 'theaters.city_id', '=', 'cities.city_id')
            ->where('cities.name', $c->city_name)
            ->count();
        echo $c->city_name . " (" . $count . " showtimes)\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
