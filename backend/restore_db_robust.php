<?php

use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "--- ROBUST RESTORE DB ---\n";

DB::statement('SET FOREIGN_KEY_CHECKS=0;');
Illuminate\Support\Facades\Artisan::call('db:wipe', ['--force' => true]);

$sqlPath = 'd:\test\Cinema-Book\123sql.sql';
if (! file_exists($sqlPath)) {
    exit("SQL file not found at $sqlPath\n");
}

$sql = file_get_contents($sqlPath);

echo 'Executing SQL... (Size: '.strlen($sql)." bytes)\n";

try {
    // Attempt full execution
    DB::unprepared($sql);
    echo "SQL Executed Successfully.\n";
} catch (\Exception $e) {
    echo 'SQL Error: '.$e->getMessage()."\n";
    // If full execution fails, try statement by statement?
    // It's messy but let's see the error first.
}

DB::statement('SET FOREIGN_KEY_CHECKS=1;');

// Verify tables
$tables = DB::select('SHOW TABLES');
echo 'Tables found: '.count($tables)."\n";
foreach ($tables as $t) {
    echo ' - '.array_values((array) $t)[0]."\n";
}

// Update dates if showtimes exist
if (Schema::hasTable('showtimes')) {
    $today = '2026-01-08';
    echo "Updating showtimes to $today...\n";
    $showtimes = Showtime::all();
    foreach ($showtimes as $s) {
        $originalTime = Carbon::parse($s->show_time);
        $s->show_date = $today;
        $s->start_time = Carbon::parse($today.' '.$originalTime->format('H:i:s'));
        $s->save();
    }
    echo 'Updated '.$showtimes->count()." showtimes.\n";
} else {
    echo "Showtimes table still missing!\n";
}
