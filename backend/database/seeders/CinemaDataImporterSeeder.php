<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Movie;
use App\Models\Hashtag;
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
            if (isset($item['image'])) {
                $item['bannerImage'] = $item['image'];
                unset($item['image']);
            }
            $combinedMovies[$title] = array_merge($combinedMovies[$title] ?? [], $item);
        }

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

            // Flatten Cast & Crew
            $actors = [];
            $directors = [];
            if (isset($data['cast&crew']) && is_array($data['cast&crew'])) {
                foreach ($data['cast&crew'] as $person) {
                    if (!isset($person['name'])) continue;
                    // Simplistic heuristic: if mostly appearing as Director or based on role
                    // Since JSON structure varies, we'll just put everyone in Actor for now or specific if role exists
                    // Assuming 'role' field exists or we just dump names.
                    // Checking implementation of details.json usually has mixed data.
                    // For now, let's just take names.
                    $actors[] = $person['name'];
                }
            }
            $actorString = implode(', ', array_slice($actors, 0, 5)); // Take top 5
            
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
                    'actor' => $actorString,
                    'director' => 'Unknown', // Placeholder as JSON often lacks clear director key in cast&crew list for this format
                ]
            );

            // Handle Genres via Hashtags
            if (isset($data['genres'])) {
                $hashtagIds = [];
                foreach ($data['genres'] as $genreName) {
                    if (in_array($genreName, ['In Theaters', 'Coming Soon']))
                        continue;

                    $hashtag = Hashtag::firstOrCreate(
                        ['name' => $genreName],
                        ['type' => 'genre']
                    );
                    $hashtagIds[] = $hashtag->hashtag_id;
                }
                $movie->hashtags()->sync($hashtagIds);
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
