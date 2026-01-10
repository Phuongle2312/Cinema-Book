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
        // Define Cities and their Theaters
        $cities = [
            'Hồ Chí Minh' => ['ho-chi-minh', [
                ['name' => 'CGV Vincom Đồng Khởi', 'address' => '72 Lê Thánh Tôn, Q.1'],
                ['name' => 'CGV Sư Vạn Hạnh', 'address' => 'Vạn Hạnh Mall, Q.10'],
                ['name' => 'Galaxy Nguyễn Du', 'address' => '116 Nguyễn Du, Q.1']
            ]],
            'Hà Nội' => ['ha-noi', [
                ['name' => 'CGV Vincom Bà Triệu', 'address' => '191 Bà Triệu'],
                ['name' => 'Lotte Cinema Landmark', 'address' => 'Keangnam Tower'],
                ['name' => 'BHD Star Discovery', 'address' => '302 Cầu Giấy']
            ]],
            'Đà Nẵng' => ['da-nang', [
                ['name' => 'CGV Vĩnh Trung Plaza', 'address' => '255 Hùng Vương'],
                ['name' => 'Lotte Cinema Đà Nẵng', 'address' => 'Tầng 5 Lotte Mart']
            ]],
            'Cần Thơ' => ['can-tho', [['name' => 'CGV Sense City', 'address' => '1 Đại Lộ Hòa Bình']]],
            'Đồng Nai' => ['dong-nai', [['name' => 'CGV BigC Đồng Nai', 'address' => 'Xa Lộ Hà Nội, Biên Hòa']]],
            'Hải Phòng' => ['hai-phong', [['name' => 'CGV AEON Mall', 'address' => 'Lê Chân, Hải Phòng']]],
            'Quảng Ninh' => ['quang-ninh', [['name' => 'CGV Vincom Hạ Long', 'address' => 'Khu Cột Đồng Hồ, Hạ Long']]],
        ];

        $movies = Movie::all();

        foreach ($cities as $cityName => $data) {
            $slug = $data[0];
            $theatersList = $data[1];

            // 1. Create City
            $city = City::firstOrCreate(
                ['slug' => $slug],
                ['name' => $cityName, 'country' => 'Vietnam', 'timezone' => 'Asia/Ho_Chi_Minh']
            );

            foreach ($theatersList as $theaterData) {
                // 2. Create Theater
                $theater = Theater::firstOrCreate(
                    ['slug' => Str::slug($theaterData['name'])],
                    [
                        'city_id' => $city->city_id,
                        'name' => $theaterData['name'],
                        'address' => $theaterData['address'],
                        'phone' => '1900 6017',
                        'description' => 'Rạp chiếu phim tiêu chuẩn quốc tế.',
                        'is_active' => true,
                    ]
                );

                // 3. Create Rooms (2 rooms per theater)
                $roomTypes = [
                    ['name' => 'Room 1 (IMAX)', 'type' => 'IMAX'],
                    ['name' => 'Room 2 (Standard)', 'type' => 'standard']
                ];
                
                foreach ($roomTypes as $rType) {
                    $room = Room::firstOrCreate(
                        ['name' => $rType['name'], 'theater_id' => $theater->theater_id],
                        [
                            'total_seats' => 50,
                            'screen_type' => $rType['type'],
                        ]
                    );

                    // 4. Create Seats
                    $this->createSeatsForRoom($room);

                    // 5. Create Showtimes (14 days)
                    if ($movies->count() > 0) {
                        $this->createShowtimesForRoom($room, $movies);
                    }
                }
            }
        }
    }

    private function createSeatsForRoom($room)
    {
        // Check if seats exist to avoid redundant checks per seat
        if (Seat::where('room_id', $room->room_id)->exists()) return;

        $rows = ['A', 'B', 'C', 'D', 'E'];
        foreach ($rows as $row) {
            for ($col = 1; $col <= 10; $col++) {
                $seatType = 'standard';
                $extra = 0;
                
                if ($row === 'E') {
                    $seatType = 'couple';
                    $extra = 80000;
                } elseif ($row === 'D') {
                    $seatType = 'vip';
                    $extra = 20000;
                }

                Seat::create([
                    'room_id' => $room->room_id,
                    'row' => $row,
                    'number' => $col,
                    'seat_code' => $row . $col,
                    'seat_type' => $seatType,
                    'is_available' => true,
                    'extra_price' => $extra
                ]);
            }
        }
    }

    private function createShowtimesForRoom($room, $movies)
    {
        // Create 14 days schedule
        for ($i = 0; $i < 14; $i++) {
            $date = Carbon::today()->addDays($i);
            
            // Standard slots
            $slots = ['10:00', '13:00', '16:00', '19:00', '22:00'];
            
            foreach ($slots as $idx => $time) {
                // Rotate movies
                $movie = $movies[$idx % $movies->count()];

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
