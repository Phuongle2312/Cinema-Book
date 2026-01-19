<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TranslateMovieSeeder extends Seeder
{
    public function run()
    {
        $movieMap = [
            'CỨU' => 'The Rescue',
            'LINH TRƯỞNG NGƯỜI: ĐĂNG XUẤT' => 'Primate Protocol: Logout',
            'BẰNG CHỨNG SINH TỬ' => 'Evidence of Life',
            'RỐI TỰ DO' => 'Freefall',
            'TRONG SƯƠNG' => 'Into The Mist',
            'NHÀ BÀ NỮ' => 'The House of Nu',
            'LẬT MẶT' => 'Face Off',
            'MAI' => 'Mai',
            'ĐẤT RỪNG PHƯƠNG NAM' => 'Song of the South',
            'QUỶ CẨU' => 'Demon Dog',
            'KẺ ĂN HỒN' => 'The UI Eater',
            'NGƯỜI VỢ CUỐI CÙNG' => 'The Last Wife',
            'CHỊ CHỊ EM EM' => 'Sister Sister',
            'BỐ GIÀ' => 'Dad, I\'m Sorry',
            'TIỆC TRĂNG MÁU' => 'Blood Moon Party',
            // Extended Mappings
            'HÀM CÁ MẬP' => 'The Shark',
            'LINH TRƯỞNG' => 'Primate Protocol',
            'CHUỘT NHÍ' => 'Super Speedy Mouse',
            'NHÀ TRẤN QUỶ' => 'The Haunted Guild',
            'HỔ CÁNH CỤT' => 'The Jungle Bunch 2',
            'CÔNG TỬ BẠC LIÊU' => 'The Prince of Bac Lieu',
            'NGÀY XƯA CÓ MỘT CHUYỆN TÌNH' => 'Once Upon a Love Story',
            'LÀM GIÀU VỚI MA' => 'Rich with Ghost',
            'ĐỊA ĐẠO' => 'The Tunnels',
            'GẶP LẠI CHỊ BẦU' => 'Meet The Pregnant Sister Again',
        ];

        foreach ($movieMap as $vn => $en) {
            DB::table('movies')
                ->where('title', 'LIKE', "%$vn%")
                ->update([
                    'title' => $en,
                    'slug' => Str::slug($en)
                ]);

            // Also update description if it contains Vietnamese keywords? 
            // Might be risky, but let's assume descriptions are mostly okay or handled separately.
        }

        $this->command->info('Translated Movie Titles to English.');
    }
}
