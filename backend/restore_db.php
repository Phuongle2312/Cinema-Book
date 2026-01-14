<?php

use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "--- RESTORING DATABASE FROM 123sql.sql ---\n";

// 1. Disable Foreign Keys
DB::statement('SET FOREIGN_KEY_CHECKS=0;');

// 2. Import SQL
// We use db:wipe first to be clean
echo "Wiping database...\n";
Illuminate\Support\Facades\Artisan::call('db:wipe', ['--force' => true]);

echo "Importing SQL...\n";
$sql = file_get_contents('d:\test\Cinema-Book\123sql.sql');
DB::unprepared($sql);

// 3. Enable Foreign Keys
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "Database restored.\n";

// 4. Update Showtimes to TODAY (2026-01-08)
// Metadata says today is 2026-01-08.
$today = '2026-01-08';
echo "Updating showtimes to date: $today\n";

$showtimes = Showtime::all();
foreach ($showtimes as $s) {
    // Keep the original time, just change the date
    // Original might be 2026-01-05 10:00:00
    // New should be 2026-01-08 10:00:00

    // Parse original time
    $originalTime = Carbon::parse($s->show_time); // 10:00

    $s->show_date = $today;
    $s->start_time = Carbon::parse($today.' '.$originalTime->format('H:i:s'));

    // Save
    $s->save();
}

$count = Showtime::whereDate('start_time', $today)->count();
echo "Updated $count showtimes to $today.\n";
echo "Done.\n";
