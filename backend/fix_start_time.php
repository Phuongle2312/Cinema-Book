<?php

use App\Models\Showtime;
use Carbon\Carbon;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$showtimes = Showtime::all();
$count = 0;

foreach ($showtimes as $showtime) {
    if (! $showtime->start_time) {
        // Handle show_date being string or Carbon
        $dateStr = is_string($showtime->show_date) ? $showtime->show_date : $showtime->show_date->format('Y-m-d');
        // If show_date contains Time part (from DB datetime column cast), strip it
        $dateStr = substr($dateStr, 0, 10);

        $dateTimeStr = $dateStr.' '.$showtime->show_time;

        try {
            $showtime->start_time = Carbon::parse($dateTimeStr);
            $showtime->save();
            $count++;
        } catch (\Exception $e) {
            echo "Error processing showtime {$showtime->showtime_id}: ".$e->getMessage()."\n";
        }
    }
}

echo "Fixed start_time for {$count} showtimes.\n";
