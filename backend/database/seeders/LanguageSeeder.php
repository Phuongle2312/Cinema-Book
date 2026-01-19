<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;
use App\Models\Movie;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Language::truncate();
        DB::table('movie_language')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $languages = [
            ['name' => 'English', 'code' => 'en'],
            ['name' => 'Vietnamese', 'code' => 'vi'],
            ['name' => 'Korean', 'code' => 'ko'],
            ['name' => 'Japanese', 'code' => 'ja'],
        ];

        foreach ($languages as $lang) {
            Language::create($lang);
        }

        $this->command->info('Seeded Languages.');

        // Attach to Movies
        $movies = Movie::all();
        $english = Language::where('code', 'en')->first();
        $vietnamese = Language::where('code', 'vi')->first();
        $korean = Language::where('code', 'ko')->first();
        $japanese = Language::where('code', 'ja')->first();

        if (!$english || !$vietnamese || !$korean || !$japanese) {
            $this->command->error('Languages not found after seeding!');
            return;
        }

        foreach ($movies as $movie) {
            $title = strtolower($movie->title);
            $langId = $english->language_id; // Default

            if (str_contains($title, 'mai') || str_contains($title, 'bo gia') || str_contains($title, 'dad') || str_contains($title, 'pregnant') || str_contains($title, 'ghost') || str_contains($title, 'bac lieu') || str_contains($title, 'tunnels') || str_contains($title, 'lat mat') || str_contains($title, 'face off')) {
                $langId = $vietnamese->language_id;
            } elseif (str_contains($title, 'chainsaw') || str_contains($title, 'anime')) {
                $langId = $japanese->language_id;
            } elseif (str_contains($title, 'exhuma') || str_contains($title, 'past lives')) {
                $langId = $korean->language_id;
            }

            // Attach Main Language
            DB::table('movie_language')->insert([
                'movie_id' => $movie->movie_id,
                'language_id' => $langId,
                'type' => 'subtitle', // Default
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add English as secondary for VN/KR/JP movies
            if ($langId != $english->language_id) {
                DB::table('movie_language')->insert([
                    'movie_id' => $movie->movie_id,
                    'language_id' => $english->language_id,
                    'type' => 'subtitle',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->command->info('Attached Languages to Movies.');
    }
}
