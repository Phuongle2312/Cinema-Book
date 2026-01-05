<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Theater;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Movie;
use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CinemaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create City
        $city = City::firstOrCreate(
            ['slug' => 'ho-chi-minh'],
            [
                'name' => 'Hồ Chí Minh',
                'country' => 'Vietnam',
                'timezone' => 'Asia/Ho_Chi_Minh'
            ]
        );

        // 2. Create Theater
        $theater = Theater::firstOrCreate(
            ['slug' => 'cgv-vincom-dong-khoi'],
            [
                'city_id' => $city->city_id,
                'name' => 'CGV Vincom Đồng Khởi',
                'address' => 'Tầng 3, TTTM Vincom Center B, 72 Lê Thánh Tôn, Bến Nghé, Quận 1',
                'phone' => '1900 6017',
                'description' => 'Rạp chiếu phim hiện đại nhất tại trung tâm thành phố.',
                'is_active' => true,
            ]
        );

        // 3. Create Room
        $room = Room::firstOrCreate(
            ['name' => 'Room 1', 'theater_id' => $theater->theater_id],
            [
                'total_seats' => 50, // 5 rows * 10 columns
                'screen_type' => 'IMAX',
            ]
        );

        // 4. Create Seats (5 rows A-E, 10 columns 1-10)
        $rows = ['A', 'B', 'C', 'D', 'E'];
        foreach ($rows as $rowIndex => $row) {
            for ($col = 1; $col <= 10; $col++) {
                $seatType = 'standard';
                $price = 100000;

                if ($row === 'E') {
                    $seatType = 'couple';
                    $price = 180000;
                } elseif ($row === 'D') {
                    $seatType = 'vip';
                    $price = 120000;
                }

                Seat::firstOrCreate(
                    [
                        'room_id' => $room->room_id,
                        'row' => $row,
                        'number' => $col,
                    ],
                    [
                        'seat_code' => $row . $col,
                        'seat_type' => $seatType,
                        'is_available' => true,
                    ]
                );
            }
        }

        // 5. Create Showtimes for existing movies
        $movies = Movie::all();
        if ($movies->count() > 0) {
            foreach ($movies as $index => $movie) {
                // Create showtimes for today and tomorrow
                for ($i = 0; $i < 2; $i++) {
                    $date = Carbon::today()->addDays($i);
                    $startTimes = ['10:00', '14:00', '18:00', '21:00'];

                    foreach ($startTimes as $time) {
                        Showtime::firstOrCreate(
                            [
                                'movie_id' => $movie->movie_id,
                                'room_id' => $room->room_id,
                                'show_date' => $date->format('Y-m-d'),
                                'show_time' => $time,
                            ],
                            [
                                'base_price' => 100000,
                                'vip_price' => 120000,
                                'is_active' => true,
                                'available_seats' => 50,
                            ]
                        );
                    }
                }
            }
        }
    }
}
