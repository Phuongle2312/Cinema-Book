<?php
use Illuminate\Support\Facades\DB;
use App\Models\Showtime;
use Carbon\Carbon;

echo "--- SELECTIVE RESTORE ---\n";

$sql = file_get_contents('d:\test\Cinema-Book\123sql.sql');

$tables = ['theaters', 'rooms', 'seats', 'showtimes', 'seat_locks', 'booking_details'];

DB::statement('SET FOREIGN_KEY_CHECKS=0;');

foreach ($tables as $table) {
    echo "Processing $table...\n";
    
    // 1. Drop if exists
    DB::statement("DROP TABLE IF EXISTS `$table`");

    // 2. Extract CREATE TABLE
    // Matches: CREATE TABLE `tableName` ( ... );
    // Note: The dump format is CREATE TABLE `xyz` (\n  ... \n) ENGINE=...;
    $patternCreate = "/CREATE TABLE `$table`\s*\(.*?\)\s*ENGINE=[^;]+;/s";
    if (preg_match($patternCreate, $sql, $matches)) {
        try {
            DB::unprepared($matches[0]);
            echo " - Created table $table.\n";
        } catch (\Exception $e) {
            echo " - Error creating $table: " . $e->getMessage() . "\n";
        }
    } else {
        echo " - CREATE statement not found for $table.\n";
    }

    // 3. Extract INSERT INTO
    // Matches: INSERT INTO `tableName` (...) VALUES ...;
    // Note: Might be multiple inserts or one huge one.
    $patternInsert = "/INSERT INTO `$table`\s*.*?;/s";
    if (preg_match_all($patternInsert, $sql, $matches)) {
        $count = 0;
        foreach ($matches[0] as $stmt) {
            try {
                DB::unprepared($stmt);
                $count++;
            } catch (\Exception $e) {
                echo " - Error inserting into $table: " . $e->getMessage() . "\n";
            }
        }
        echo " - Executed $count INSERT statements for $table.\n";
    } else {
        echo " - No INSERT statements found for $table.\n";
    }
}

DB::statement('SET FOREIGN_KEY_CHECKS=1;');

// Update dates
if (Schema::hasTable('showtimes')) {
    $today = '2026-01-08';
    echo "Updating showtimes to $today...\n";
    $showtimes = Showtime::all();
    foreach ($showtimes as $s) {
        if (!$s->show_time) continue;
        $originalTime = Carbon::createFromFormat('H:i:s', $s->show_time); 
        // Note: show_time in dump is '10:00' (H:i) but sometimes matches 10:00:00
        // Adjust parsing if needed.
        if (!$originalTime) $originalTime = Carbon::parse($s->show_time);

        $s->show_date = $today;
        $s->start_time = Carbon::parse($today . ' ' . $originalTime->format('H:i:s'));
        $s->save();
    }
    echo "Updated " . $showtimes->count() . " showtimes.\n";
}
