<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Theater;
use App\Models\Room;
use App\Models\Movie;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CinemaSeeder extends Seeder
{
    public function run(): void
    {
        echo "Cleaning tables..." . PHP_EOL;
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('showtimes')->truncate();
        DB::table('seats')->truncate();
        DB::table('rooms')->truncate();
        DB::table('theaters')->truncate();
        DB::table('cities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $citiesData = [
            [
                'slug' => 'ho-chi-minh',
                'name' => 'Hồ Chí Minh',
                'theater_slug' => 'cgv-vincom-dong-khoi',
                'theater_name' => 'CGV Vincom Đồng Khởi',
                'theater_address' => '72 Lê Thánh Tôn, Quận 1',
            ],
            [
                'slug' => 'ha-noi',
                'name' => 'Hà Nội',
                'theater_slug' => 'cgv-vincom-nguyen-chi-thanh',
                'theater_name' => 'CGV Vincom Nguyễn Chí Thanh',
                'theater_address' => '54A Nguyễn Chí Thanh, Quận Đống Đa',
            ],
            [
                'slug' => 'da-nang',
                'name' => 'Đà Nẵng',
                'theater_slug' => 'cgv-vincom-da-nang',
                'theater_name' => 'CGV Vincom Đà Nẵng',
                'theater_address' => 'Ngô Quyền, Sơn Trà',
            ],
        ];

        $movies = Movie::all();
        echo "Found " . $movies->count() . " movies." . PHP_EOL;

        foreach ($citiesData as $c) {
            $cityId = DB::table('cities')->insertGetId([
                'name' => $c['name'],
                'slug' => $c['slug'],
                'country' => 'Vietnam',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $theaterId = DB::table('theaters')->insertGetId([
                'city_id' => $cityId,
                'name' => $c['theater_name'],
                'slug' => $c['theater_slug'],
                'address' => $c['theater_address'],
                'phone' => '1900 6017',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            for ($r = 1; $r <= 2; $r++) {
                $roomId = DB::table('rooms')->insertGetId([
                    'theater_id' => $theaterId,
                    'name' => "Room $r - " . $c['slug'],
                    'total_seats' => 50,
                    'screen_type' => ($r == 1 ? 'standard' : 'vip'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Seats
                $seats = [];
                $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
                foreach ($rows as $row) {
                    $colsInRow = 10;
                    for ($col = 1; $col <= $colsInRow; $col++) {
                        $type = 'standard';
                        // $extra = 0; // Column removed

                        if (in_array($row, ['D', 'E', 'F', 'G'])) {
                            $type = 'vip';
                            // $extra = 20000;
                        } elseif ($row === 'H') {
                            $type = 'couple';
                            // $extra = 50000;
                        }

                        $seats[] = [
                            'room_id' => $roomId,
                            'row' => $row,
                            'number' => $col,
                            'seat_code' => $row . $col,
                            'type' => $type, // Keeping original column if exists
                            'seat_type' => $type, // New column
                            // 'extra_price' => $extra, // Removed
                            'is_available' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                DB::table('seats')->insert($seats);

                // Showtimes
                $timeSlots = ['09:00', '12:00', '15:00', '18:00', '21:00'];
                foreach ($movies as $mIdx => $movie) {
                    if (($r == 1 && $mIdx < 3) || ($r == 2 && $mIdx >= 3)) {
                        for ($day = 0; $day < 7; $day++) {
                            $date = Carbon::today()->addDays($day)->format('Y-m-d');
                            $slots = ($mIdx % 3 == 0) ? [0, 3] : (($mIdx % 3 == 1) ? [1, 4] : [2]);

                            foreach ($slots as $sIdx) {
                                DB::table('showtimes')->insert([
                                    'movie_id' => $movie->movie_id,
                                    'room_id' => $roomId,
                                    'start_time' => $date . ' ' . $timeSlots[$sIdx],
                                    'base_price' => ($r == 1 ? 80000 : 110000),
                                    'status' => 'scheduled',
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }
            echo "Finished City: " . $c['name'] . PHP_EOL;
        }
    }
}
