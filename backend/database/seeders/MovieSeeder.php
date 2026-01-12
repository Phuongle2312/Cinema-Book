<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $moviesJson = file_get_contents(base_path('../frontend/src/data/movies.json'));
        $moviesData = json_decode($moviesJson, true);

        $detailsJson = file_get_contents(base_path('../frontend/src/data/details.json'));
        $detailsData = json_decode($detailsJson, true);

        // Combine data using title as key
        $combinedData = [];
        foreach ($moviesData as $movie) {
            $combinedData[$movie['title']] = $movie;
        }

        foreach ($detailsData as $detail) {
            if (isset($combinedData[$detail['title']])) {
                $combinedData[$detail['title']] = array_merge($combinedData[$detail['title']], $detail);
            } else {
                $combinedData[$detail['title']] = $detail;
            }
        }

        foreach ($combinedData as $title => $item) {
            // Convert duration "1h 40m" to minutes
            $minutes = 0;
            if (isset($item['duration'])) {
                if (preg_match('/(\d+)h/', $item['duration'], $matches)) {
                    $minutes += intval($matches[1]) * 60;
                }
                if (preg_match('/(\d+)m/', $item['duration'], $matches)) {
                    $minutes += intval($matches[1]);
                }
            }

            $movie = Movie::updateOrCreate(
                ['title' => $title],
                [
                    'slug' => Str::slug($title),
                    'description' => $item['description'] ?? '',
                    'duration' => $minutes ?: 120,
                    'release_date' => Carbon::createFromDate($item['year'] ?? 2025, 1, 1),
                    'poster_url' => $item['image'] ?? null,
                    'banner_url' => $item['bannerImage'] ?? ($item['image'] ?? null),
                    'trailer_url' => $item['trailer'] ?? null,
                    'status' => 'now_showing',
                    'is_featured' => true, // Mark all as featured for now to fill the section
                ]
            );

            // Handle Genres
            if (isset($item['genres'])) {
                $genreIds = [];
                foreach ($item['genres'] as $genreName) {
                    if ($genreName === 'In Theaters')
                        continue; // Skip non-genre tags
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
