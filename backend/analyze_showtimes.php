<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // 1. Get unique dates from showtimes
    $dates = DB::table('showtimes')
        ->select(DB::raw('DATE(start_time) as show_date'), DB::raw('COUNT(*) as count'))
        ->groupBy('show_date')
        ->orderBy('show_date')
        ->get();

    echo "Available Dates:\n";
    foreach ($dates as $d) {
        echo $d->show_date.' ('.$d->count." showtimes)\n";
    }

    // 2. Get unique cities from theaters connected to showtimes
    $cities = DB::table('showtimes')
        ->join('rooms', 'showtimes.screen_id', '=', 'rooms.screen_id') // Wait, column is screen_id or room_id? Check migration.
        ->join('theaters', 'rooms.theater_id', '=', 'theaters.theater_id')
        ->join('cities', 'theaters.city_id', '=', 'cities.city_id')
        ->select('cities.name as city_name', DB::raw('COUNT(*) as count'))
        ->groupBy('cities.name')
        ->get();

    echo "\nAvailable Cities for Showtimes:\n";
    foreach ($cities as $c) {
        echo $c->city_name.' ('.$c->count." showtimes)\n";
    }

    // Check column name for room/screen
    $columns = DB::getSchemaBuilder()->getColumnListing('showtimes');
    echo "\nShowtime Columns: ".implode(', ', $columns)."\n";

} catch (\Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";

    // Fallback debug
    $columns = DB::getSchemaBuilder()->getColumnListing('showtimes');
    echo 'Showtime Columns: '.implode(', ', $columns)."\n";
}
