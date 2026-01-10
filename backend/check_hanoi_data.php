<?php
use App\Models\City;
use App\Models\Theater;
use App\Models\Room;
use App\Models\Showtime;

echo "--- CHECKING HANOI DATA ---\n";

$city = City::where('name', 'Hà Nội')->first();
if (!$city) {
    die("City 'Hà Nội' NOT FOUND in DB.\n");
}
echo "City Found: " . $city->city_id . "\n";

$theaters = Theater::where('city_id', $city->city_id)->get();
echo "Theaters count: " . $theaters->count() . "\n";

foreach ($theaters as $t) {
    echo " - Theater: $t->name ($t->theater_id)\n";
    $rooms = Room::where('theater_id', $t->theater_id)->get();
    foreach ($rooms as $r) {
        echo "   * Room: $r->name ($r->room_id) [Type: $r->screen_type]\n";
        
        $showtimes = Showtime::where('room_id', $r->room_id)
            ->whereDate('start_time', '2026-01-08')
            ->get();
            
        echo "     Showtimes Today (2026-01-08): " . $showtimes->count() . "\n";
        foreach ($showtimes as $s) {
            echo "       > " . $s->start_time . " (Format: " . $s->format . ")\n";
        }
    }
}
