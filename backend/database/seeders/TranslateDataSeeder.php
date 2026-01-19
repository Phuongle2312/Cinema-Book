<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TranslateDataSeeder extends Seeder
{
    public function run()
    {
        // Disable FK checks to allow easier updates/deletes if logic ensures consistency
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 1. GENRES
        $genreMap = [
            'Hành động' => 'Action',
            'Kinh dị' => 'Horror',
            'Hài' => 'Comedy',
            'Hài hước' => 'Comedy',
            'Tình cảm' => 'Romance',
            'Lãng mạn' => 'Romance',
            'Hoạt hình' => 'Animation',
            'Khoa học viễn tưởng' => 'Sci-Fi',
            'Viễn tưởng' => 'Sci-Fi',
            'Phiêu lưu' => 'Adventure',
            'Gia đình' => 'Family',
            'Tâm lý' => 'Drama',
            'Hồi hộp' => 'Thriller',
            'Gay cấn' => 'Thriller',
            'Tài liệu' => 'Documentary',
            'Chiến tranh' => 'War',
            'Lịch sử' => 'History',
            'Âm nhạc' => 'Music',
            'Tội phạm' => 'Crime',
            'Thần thoại' => 'Fantasy',
            'Cổ trang' => 'Period Drama',
        ];

        foreach ($genreMap as $vn => $en) {
            $vnGenre = DB::table('genres')->where('name', 'LIKE', $vn)->first();
            if ($vnGenre) {
                // Check if target English genre exists
                $enGenre = DB::table('genres')->where('name', '=', $en)->first();

                if ($enGenre) {
                    // MERGE: Update movie_genre to point to enGenre, then delete vnGenre
                    try {
                        // Update links
                        DB::table('movie_genre')
                            ->where('genre_id', $vnGenre->genre_id)
                            ->update(['genre_id' => $enGenre->genre_id]);

                        // Delete old
                        DB::table('genres')->where('genre_id', $vnGenre->genre_id)->delete();
                        $this->command->info("Merged Genre '$vn' into '$en'.");
                    } catch (\Exception $e) {
                        // Duplicate entry in pivot table? Delete the duplicate link first
                        DB::table('movie_genre')->where('genre_id', $vnGenre->genre_id)->delete();
                        DB::table('genres')->where('genre_id', $vnGenre->genre_id)->delete();
                        $this->command->info("Merged Genre '$vn' into '$en' (Resolved Pivot Conflict).");
                    }
                } else {
                    // RENAME
                    try {
                        DB::table('genres')
                            ->where('genre_id', $vnGenre->genre_id)
                            ->update(['name' => $en, 'slug' => Str::slug($en)]);
                        $this->command->info("Renamed Genre '$vn' to '$en'.");
                    } catch (\Exception $e) {
                        $this->command->info("Skipped Genre '$vn' due to conflict.");
                    }
                }
            }
        }

        // 2. CITIES
        $cityMap = [
            'Hồ Chí Minh' => 'Ho Chi Minh City',
            'Hà Nội' => 'Hanoi',
            'Đà Nẵng' => 'Da Nang',
            'Cần Thơ' => 'Can Tho',
            'Hải Phòng' => 'Hai Phong',
            'Đồng Nai' => 'Dong Nai',
            'Quảng Ninh' => 'Quang Ninh',
            'Huế' => 'Hue',
            'Nha Trang' => 'Nha Trang',
            'Vũng Tàu' => 'Vung Tau',
        ];

        foreach ($cityMap as $vn => $en) {
            $vnCity = DB::table('cities')->where('name', 'LIKE', "%$vn%")->first();

            if ($vnCity) {
                $enCity = DB::table('cities')->where('name', '=', $en)->first();

                if ($enCity) {
                    // MERGE
                    DB::table('theaters')->where('city_id', $vnCity->city_id)->update(['city_id' => $enCity->city_id]);
                    DB::table('cities')->where('city_id', $vnCity->city_id)->delete();
                    $this->command->info("Merged City '$vn' into '$en'.");
                } else {
                    // RENAME
                    DB::table('cities')
                        ->where('city_id', $vnCity->city_id)
                        ->update(['name' => $en, 'slug' => Str::slug($en)]);
                    $this->command->info("Renamed City '$vn' to '$en'.");
                }
            }
        }

        // 3. THEATERS
        $theaters = DB::table('theaters')->get();
        foreach ($theaters as $theater) {
            $newName = $theater->name;
            $newAddress = $theater->address;
            $updated = false;

            $replacements = [
                'Đà Nẵng' => 'Da Nang',
                'Hà Nội' => 'Hanoi',
                'Hồ Chí Minh' => 'Ho Chi Minh',
                'Quận' => 'District',
                'Phường' => 'Ward',
                'Tầng' => 'Floor',
                'Đường' => 'Street',
                'Thành phố' => 'City',
                'Trung tâm thương mại' => 'Shopping Center',
            ];

            foreach ($replacements as $vn => $en) {
                if (str_contains($newName, $vn)) {
                    $newName = str_replace($vn, $en, $newName);
                    $updated = true;
                }
                if (str_contains($newAddress, $vn)) {
                    $newAddress = str_replace($vn, $en, $newAddress);
                    $updated = true;
                }
            }

            if ($updated) {
                try {
                    DB::table('theaters')
                        ->where('theater_id', $theater->theater_id)
                        ->update([
                            'name' => $newName,
                            'address' => $newAddress,
                            'slug' => Str::slug($newName) // May conflict, but let's try
                        ]);
                } catch (\Exception $e) {
                    // Verify if simple update without slug works?
                    DB::table('theaters')
                        ->where('theater_id', $theater->theater_id)
                        ->update([
                            'name' => $newName,
                            'address' => $newAddress,
                            // Skip slug update if it causes conflict
                        ]);
                }
            }
        }
        $this->command->info('Translated Theaters (Names/Addresses).');

        // 5. SEAT TYPES (Just in case)
        DB::table('seats')->where('type', 'Thường')->update(['type' => 'standard']);
        DB::table('seats')->where('type', 'Đôi')->update(['type' => 'couple']);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
