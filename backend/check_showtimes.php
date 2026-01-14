<?php

use App\Models\Showtime;
use Carbon\Carbon;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'Current Server Time: '.Carbon::now()->toDateTimeString()."\n";
echo 'Current Timezone: '.config('app.timezone')."\n";
echo "--------------------------------------------------\n";

$count = Showtime::count();
echo 'Total Showtimes in DB: '.$count."\n";

if ($count == 0) {
    echo "No showtimes found.\n";
    exit;
}

$showtimes = Showtime::orderBy('start_time', 'desc')->take(10)->get();

echo "Top 10 Latest Showtimes:\n";
foreach ($showtimes as $st) {
    echo "ID: {$st->showtime_id} | Start: {$st->start_time} | Movie: {$st->movie_id}\n";
}

echo "--------------------------------------------------\n";
echo "Showtimes Table Schema:\n";
$columns = \DB::select('DESCRIBE showtimes');
foreach ($columns as $col) {
    echo "Field: {$col->Field} | Type: {$col->Type} | Null: {$col->Null} | Key: {$col->Key} | Default: {$col->Default} | Extra: {$col->Extra}\n";
}

echo "--------------------------------------------------\n";
echo "Checking for UPCOMING showtimes (start_time >= now):\n";
$upcoming = Showtime::where('start_time', '>=', Carbon::now())->count();
echo "Upcoming count: $upcoming\n";
