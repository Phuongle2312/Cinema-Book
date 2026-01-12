<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $json = file_get_contents(base_path('../frontend/src/data/banner.json'));
        $data = json_decode($json, true);

        foreach ($data as $item) {
            // Convert duration "1h 40m" to minutes
            $minutes = 0;
            if (preg_match('/(\d+)h/', $item['duration'], $matches)) {
                $minutes += intval($matches[1]) * 60;
            }
            if (preg_match('/(\d+)m/', $item['duration'], $matches)) {
                $minutes += intval($matches[1]);
            }

            $movie = Movie::updateOrCreate(
                ['title' => $item['title']],
                [
                    'slug' => Str::slug($item['title']),
                    'description' => $item['description'],
                    'duration' => $minutes ?: 120,
                    'release_date' => Carbon::createFromDate($item['year'], 1, 1),
                    'poster_url' => $item['image'],
                    'banner_url' => $item['image'],
                    'status' => 'now_showing',
                    'is_featured' => true,
                ]
            );

            // Handle Genres
            if (isset($item['genres'])) {
                $genreIds = [];
                foreach ($item['genres'] as $genreName) {
                    $genre = Genre::firstOrCreate(
                        ['name' => $genreName],
                        ['slug' => Str::slug($genreName)]
                    );
                    $genreIds[] = $genre->genre_id;
                }
                $movie->genres()->sync($genreIds);
            }
        }
    }
}
