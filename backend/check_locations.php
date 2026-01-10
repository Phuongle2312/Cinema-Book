<?php
use App\Models\City;
use App\Models\Theater;
use App\Models\Room;

echo "--- LOCATIONS CHECK ---\n";

$cities = City::all();
echo "Cities Count: " . $cities->count() . "\n";
foreach ($cities as $c) {
    echo "[$c->city_id] $c->name\n";
    $theaters = Theater::where('city_id', $c->city_id)->get();
    foreach ($theaters as $t) {
        echo "  - [$t->theater_id] $t->name\n";
        $rooms = Room::where('theater_id', $t->theater_id)->get();
        foreach ($rooms as $r) {
            echo "    * [$r->room_id] $r->name ($r->screen_type)\n";
        }
    }
}
