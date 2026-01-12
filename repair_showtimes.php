<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use App\Models\Showtime;
use Carbon\Carbon;

$app->make(Kernel::class)->bootstrap();

try {
    $showtimes = Showtime::all();
    echo "Repairing " . $showtimes->count() . " showtimes..." . PHP_EOL;

    foreach ($showtimes as $s) {
        try {
            $date = $s->show_date;
            $time = $s->show_time;

            if ($date instanceof Carbon) {
                $dateStr = $date->format('Y-m-d');
            } else {
                $dateStr = Carbon::parse($date)->format('Y-m-d');
            }

            $startDateTime = Carbon::parse($dateStr . ' ' . $time);

            // Explicitly update only the start_time column using DB to avoid model events or casting issues
            \Illuminate\Support\Facades\DB::table('showtimes')
                ->where('showtime_id', $s->showtime_id)
                ->update(['start_time' => $startDateTime->toDateTimeString()]);

            echo "Repaired ID {$s->showtime_id}: " . $startDateTime->toDateTimeString() . PHP_EOL;
        } catch (Exception $e) {
            echo "FAILED ID {$s->showtime_id}: " . $e->getMessage() . PHP_EOL;
        }
    }
    echo "DONE." . PHP_EOL;
} catch (Throwable $e) {
    echo "GLOBAL ERROR: " . $e->getMessage() . PHP_EOL;
}
