<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\Theater;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CinemaSeeder extends Seeder
{
    public function run(): void
    {
        echo 'Cleaning tables...' . PHP_EOL;
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('showtimes')->truncate();
        DB::table('seats')->truncate();
        DB::table('rooms')->truncate();
        DB::table('theaters')->truncate();
        DB::table('cities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Define Cities and their Theaters (Remote version)
        // Define Cities and their Theaters (Real CGV Data - English)
        $cities = [
            'Ho Chi Minh' => [
                'ho-chi-minh',
                [
                    ['name' => 'CGV Vincom Dong Khoi', 'address' => 'Floor 3, Vincom Center B, 72 Le Thanh Ton, District 1'],
                    ['name' => 'CGV Liberty Citypoint', 'address' => 'Floor 1, Liberty Citypoint, 59 Pasteur, District 1'],
                    ['name' => 'CGV Pearl Plaza', 'address' => 'Floor 5, Pearl Plaza, 561A Dien Bien Phu, Binh Thanh District'],
                    ['name' => 'CGV SC VivoCity', 'address' => 'Floor 5, SC VivoCity, 1058 Nguyen Van Linh, District 7'],
                    ['name' => 'CGV Thao Dien Pearl', 'address' => 'Floor 2, Thao Dien Pearl, 12 Quoc Huong, District 2'],
                    ['name' => 'CGV Aeon Tan Phu', 'address' => 'Floor 3, AEON Mall Tan Phu, 30 Bo Bao Tan Thang, Tan Phu District'],
                    ['name' => 'CGV Giga Mall Thu Duc', 'address' => 'Floor 6, Giga Mall, 240-242 Pham Van Dong, Thu Duc City'],
                ],
            ],
            'Hanoi' => [
                'ha-noi',
                [
                    ['name' => 'CGV Vincom Ba Trieu', 'address' => 'Floor 6, Vincom Center, 191 Ba Trieu, Hai Ba Trung District'],
                    ['name' => 'CGV Aeon Long Bien', 'address' => 'Floor 4, AEON Mall Long Bien, 27 Co Linh, Long Bien District'],
                    ['name' => 'CGV Vincom Royal City', 'address' => 'B2 Floor, Vincom Mega Mall Royal City, 72A Nguyen Trai, Thanh Xuan District'],
                    ['name' => 'CGV Indochina Plaza', 'address' => 'IPH Shopping Center, 241 Xuan Thuy, Cau Giay District'],
                    ['name' => 'CGV Vincom Nguyen Chi Thanh', 'address' => 'Floor 6, Vincom Center, 54A Nguyen Chi Thanh, Dong Da District'],
                ],
            ],
            'Da Nang' => [
                'da-nang',
                [
                    ['name' => 'CGV Vincom Da Nang', 'address' => 'Floor 4, Vincom Center, 910A Ngo Quyen, Son Tra District'],
                    ['name' => 'CGV Vinh Trung Plaza', 'address' => '255-257 Hung Vuong, Thanh Khe District'],
                ],
            ],
            'Can Tho' => ['can-tho', [['name' => 'CGV Sense City', 'address' => 'Floor 3, Sense City, 1 Hoa Binh Avenue, Ninh Kieu District']]],
            'Dong Nai' => ['dong-nai', [['name' => 'CGV BigC Dong Nai', 'address' => 'BigC Dong Nai, National Route 51, Bien Hoa City']]],
            'Hai Phong' => ['hai-phong', [['name' => 'CGV AEON Mall Hai Phong', 'address' => 'Floor 3, AEON Mall Hai Phong Le Chan, Hai Phong']]],
            'Quang Ninh' => ['quang-ninh', [['name' => 'CGV Vincom Ha Long', 'address' => 'Floor 4, Vincom Plaza Ha Long, Bach Dang Ward, Ha Long City']]],
        ];

        $movies = Movie::all();

        foreach ($cities as $cityName => $data) {
            try {
                $slug = $data[0];
                $theatersList = $data[1];

                // 1. Create City
                echo "Creating City: $cityName\n";
                $city = City::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $cityName, 'country' => 'Vietnam', 'timezone' => 'Asia/Ho_Chi_Minh']
                );

                foreach ($theatersList as $theaterData) {
                    // 2. Create Theater
                    echo "  Creating Theater: " . $theaterData['name'] . "\n";
                    $theater = Theater::firstOrCreate(
                        ['slug' => Str::slug($theaterData['name'])],
                        [
                            'city_id' => $city->city_id,
                            'name' => $theaterData['name'],
                            'address' => $theaterData['address'],
                            'phone' => '1900 6017',
                            'description' => 'World-class standard cinema.',
                            'is_active' => true,
                        ]
                    );

                    // 3. Create Rooms
                    $roomTypes = [
                        ['name' => 'Room 1 (IMAX)', 'type' => 'IMAX'],
                        ['name' => 'Room 2 (Standard)', 'type' => 'standard'],
                    ];

                    foreach ($roomTypes as $rType) {
                        $room = Room::firstOrCreate(
                            ['name' => $rType['name'], 'theater_id' => $theater->theater_id],
                            [
                                'total_seats' => 80,
                                'screen_type' => $rType['type'],
                            ]
                        );

                        // 4. Create Seats
                        $this->createSeatsForRoom($room);

                        // 5. Create Showtimes
                        if ($movies->count() > 0) {
                            $this->createShowtimesForRoom($room, $movies);
                        }
                    }
                }
                echo 'Finished City: ' . $cityName . PHP_EOL;
            } catch (\Exception $e) {
                echo "ERROR in City $cityName: " . $e->getMessage() . "\n";
                throw $e;
            }
        }
    }

    private function createSeatsForRoom($room)
    {
        if (Seat::where('room_id', $room->room_id)->exists()) {
            return;
        }

        $seats = [];
        $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($rows as $row) {
            $colsInRow = 10;
            for ($col = 1; $col <= $colsInRow; $col++) {
                $type = 'standard';

                if (in_array($row, ['D', 'E', 'F', 'G'])) {
                    $type = 'vip';
                } elseif ($row === 'H') {
                    $type = 'couple';
                }

                $extraPrice = 0;
                if ($type === 'vip')
                    $extraPrice = 20000;
                if ($type === 'couple')
                    $extraPrice = 30000;

                $seats[] = [
                    'room_id' => $room->room_id,
                    'row' => $row,
                    'number' => $col,
                    'seat_code' => $row . $col . '-' . $room->room_id, // Ensure unique seat_code globally if constraint exists
                    'type' => $type,
                    'extra_price' => $extraPrice,
                    // 'seat_type' => $type, // Removed redundant column
                    'is_available' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('seats')->insert($seats);
    }

    private function createShowtimesForRoom($room, $movies)
    {
        for ($i = 0; $i < 7; $i++) {
            $currentDate = Carbon::today()->addDays($i);

            // Start the day at 9:00 AM
            $startTime = $currentDate->copy()->setTime(9, 0, 0);

            // End the day around 11:00 PM
            $endTimeLimit = $currentDate->copy()->setTime(23, 0, 0);

            while ($startTime->lt($endTimeLimit)) {
                // Pick a random movie
                $movie = $movies->random();

                // Validate duration
                $duration = $movie->duration && $movie->duration > 0 ? $movie->duration : 90;

                try {
                    // Create Showtime
                    Showtime::firstOrCreate(
                        [
                            'movie_id' => $movie->movie_id,
                            'room_id' => $room->room_id,
                            'start_time' => $startTime->toDateTimeString(),
                        ],
                        [
                            'base_price' => ($room->screen_type === 'IMAX' ? 120000 : ($startTime->isWeekend() ? 100000 : 80000)),
                            'status' => 'scheduled',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                } catch (\Exception $e) {
                    echo "  Error creating showtime at " . $startTime->toDateTimeString() . ": " . $e->getMessage() . "\n";
                    // Skip this slot if duplicate/error
                }

                // Calculate next start time: Duration + 30 mins cleaning/ads
                // Round up to nearest 5 minutes
                $totalDuration = $duration + 30; // buffer
                $startTime->addMinutes($totalDuration);

                // Round to nearest 5
                $timestamp = $startTime->timestamp;
                $roundedTimestamp = ceil($timestamp / 300) * 300;
                $startTime = Carbon::createFromTimestamp($roundedTimestamp);
            }
        }
    }
}
