<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Movie;
use App\Models\Genre;
use App\Models\Cast;
use App\Models\Promotion;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class CinemaDataImporterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->importMovies();
        $this->importEvents();
    }

    private function importMovies()
    {
        $moviesPath = base_path('../frontend/src/data/movies.json');
        $detailsPath = base_path('../frontend/src/data/details.json');
        $bannerPath = base_path('../frontend/src/data/banner.json');
        $trailerPath = base_path('../frontend/src/data/trailer.json');

        $moviesData = File::exists($moviesPath) ? json_decode(File::get($moviesPath), true) : [];
        $detailsData = File::exists($detailsPath) ? json_decode(File::get($detailsPath), true) : [];
        $bannerData = File::exists($bannerPath) ? json_decode(File::get($bannerPath), true) : [];
        $trailerData = File::exists($trailerPath) ? json_decode(File::get($trailerPath), true) : [];

        // Combine all movie data by title
        $combinedMovies = [];

        foreach ($moviesData as $item) {
            $title = $item['title'];
            $combinedMovies[$title] = array_merge($combinedMovies[$title] ?? [], $item);
        }

        foreach ($detailsData as $item) {
            $title = $item['title'];
            $combinedMovies[$title] = array_merge($combinedMovies[$title] ?? [], $item);
        }

        foreach ($bannerData as $item) {
            $title = $item['title'];
            // For banner data, the 'image' is usually the banner image
            if (isset($item['image'])) {
                $item['bannerImage'] = $item['image'];
                unset($item['image']);
            }
            $combinedMovies[$title] = array_merge($combinedMovies[$title] ?? [], $item);
        }

        // Handle trailer.json
        foreach ($trailerData as $index => $item) {
            $title = null;
            foreach ($moviesData as $m) {
                if ($m['id'] == $item['id']) {
                    $title = $m['title'];
                    break;
                }
            }
            if ($title && isset($combinedMovies[$title])) {
                $combinedMovies[$title]['trailer'] = $item['trailer'];
            }
        }

        foreach ($combinedMovies as $title => $data) {
            // Parse duration
            $durationMinutes = 0;
            if (isset($data['duration'])) {
                if (preg_match('/(\d+)h/', $data['duration'], $matches)) {
                    $durationMinutes += intval($matches[1]) * 60;
                }
                if (preg_match('/(\d+)m/', $data['duration'], $matches)) {
                    $durationMinutes += intval($matches[1]);
                }
            }
            if ($durationMinutes == 0)
                $durationMinutes = 120; // Default

            // Parse status
            $status = 'now_showing';
            if (isset($data['genres']) && in_array('Coming Soon', $data['genres'])) {
                $status = 'coming_soon';
            }

            $movie = Movie::updateOrCreate(
                ['title' => $title],
                [
                    'slug' => Str::slug($title),
                    'description' => $data['description'] ?? '',
                    'duration' => $durationMinutes,
                    'release_date' => Carbon::createFromDate($data['year'] ?? now()->year, 1, 1),
                    'poster_url' => $data['image'] ?? ($data['poster_url'] ?? null),
                    'banner_url' => $data['bannerImage'] ?? ($data['banner_url'] ?? null),
                    'trailer_url' => $data['trailer'] ?? ($data['trailer_url'] ?? null),
                    'status' => $status,
                    'rating' => $data['rating'] ?? 0,
                    'is_featured' => true,
                ]
            );

            // Handle Genres
            if (isset($data['genres'])) {
                $genreIds = [];
                foreach ($data['genres'] as $genreName) {
                    if (in_array($genreName, ['In Theaters', 'Coming Soon']))
                        continue;

                    $genre = Genre::firstOrCreate(
                        ['name' => $genreName],
                        ['slug' => Str::slug($genreName)]
                    );
                    $genreIds[] = $genre->genre_id;
                }
                $movie->genres()->sync($genreIds);
            }

            // Handle Cast & Crew
            if (isset($data['cast&crew']) && is_array($data['cast&crew'])) {
                $castIdsWithPivot = [];
                foreach ($data['cast&crew'] as $index => $person) {
                    if (isset($person['price']))
                        continue;
                    if (!isset($person['name']))
                        continue;

                    $cast = Cast::firstOrCreate(
                        ['name' => $person['name']],
                        [
                            'avatar' => $person['img'] ?? null,
                            'type' => 'actor'
                        ]
                    );
                    $castIdsWithPivot[$cast->cast_id] = [
                        'role' => 'actor',
                        'character_name' => '',
                    ];
                }
                if (!empty($castIdsWithPivot)) {
                    $movie->cast()->sync($castIdsWithPivot);
                }
            }
        }
    }

    private function importEvents()
    {
        $eventsPath = base_path('../frontend/src/data/events.json');
        if (!File::exists($eventsPath))
            return;

        $eventsData = json_decode(File::get($eventsPath), true);

        foreach ($eventsData as $item) {
            $code = strtoupper(Str::slug($item['title'], '_'));

            Promotion::updateOrCreate(
                ['code' => $code],
                [
                    'description' => $item['description'],
                    'discount_type' => 'percentage',
                    'discount_value' => ($item['type'] === 'offer' ? 10 : 0),
                    'is_active' => true,
                    'valid_from' => now(),
                    'valid_to' => now()->addMonths(6),
                ]
            );
        }
    }
}
