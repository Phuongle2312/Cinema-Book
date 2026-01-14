<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Movie;
use App\Models\Hashtag;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SeedMoreMovies extends Seeder
{
    public function run()
    {
        $movies = [
            [
                'title' => 'Dune: Part Two',
                'description' => 'Paul Atreides unites with Chani and the Fremen while on a warpath of revenge against the conspirators who destroyed his family.',
                'duration' => 166,
                'release_date' => Carbon::now()->subDays(10),
                'poster_url' => 'https://image.tmdb.org/t/p/w500/1pdfLvkbY9ohJlCjQH2CZjjYVvJ.jpg',
                'status' => 'now_showing',
                'genre' => ['Science Fiction', 'Adventure']
            ],
            [
                'title' => 'Kung Fu Panda 4',
                'description' => 'Po must train a new warrior when he is chosen to become the spiritual leader of the Valley of Peace.',
                'duration' => 94,
                'release_date' => Carbon::now()->subDays(5),
                'poster_url' => 'https://image.tmdb.org/t/p/w500/kDp1vUBnMpe8ak4rjgl3cLELqjU.jpg',
                'status' => 'now_showing',
                'genre' => ['Animation', 'Action', 'Comedy']
            ],
            [
                'title' => 'Civil War',
                'description' => 'A journey across a dystopian future America, following a team of military-embedded journalists as they race against time to reach DC before rebel factions descend upon the White House.',
                'duration' => 109,
                'release_date' => Carbon::now()->addDays(15),
                'poster_url' => 'https://image.tmdb.org/t/p/w500/sh7Rg8Er3tFcN9BpKIPOMvALgZd.jpg',
                'status' => 'coming_soon',
                'genre' => ['War', 'Action', 'Drama']
            ],
            [
                'title' => 'Godzilla x Kong: The New Empire',
                'description' => 'Following their explosive showdown, Godzilla and Kong must reunite against a colossal undiscovered threat hidden within our world.',
                'duration' => 115,
                'release_date' => Carbon::now()->addDays(5),
                'poster_url' => 'https://image.tmdb.org/t/p/w500/tMefBSflR6PGQLv7WvFPpKLZkyk.jpg',
                'status' => 'coming_soon',
                'genre' => ['Action', 'Science Fiction', 'Adventure']
            ],
            [
                'title' => 'The Fall Guy',
                'description' => 'A battered and past-his-prime stuntman finds himself working on a movie set with the star for whom he doubled long ago, who has gone missing.',
                'duration' => 126,
                'release_date' => Carbon::now()->addDays(20),
                'poster_url' => 'https://image.tmdb.org/t/p/w500/a2tys4sD7xzVaogP1TPKdguSMD.jpg',
                'status' => 'coming_soon',
                'genre' => ['Action', 'Comedy']
            ],
            [
                'title' => 'Oppenheimer',
                'description' => 'The story of American scientist J. Robert Oppenheimer and his role in the development of the atomic bomb.',
                'duration' => 180,
                'release_date' => Carbon::now()->subMonths(6),
                'poster_url' => 'https://image.tmdb.org/t/p/w500/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg',
                'status' => 'ended',
                'genre' => ['Drama', 'History']
            ]
        ];

        foreach ($movies as $data) {
            $movie = Movie::updateOrCreate(
                ['title' => $data['title']],
                [
                    'slug' => Str::slug($data['title']),
                    'description' => $data['description'],
                    'duration' => $data['duration'],
                    'release_date' => $data['release_date'],
                    'poster_url' => $data['poster_url'],
                    'banner_url' => $data['poster_url'],
                    'status' => $data['status'],
                    'base_price' => 100000,
                ]
            );

            // Sync Genres
            $hashtagIds = [];
            foreach ($data['genre'] as $genreName) {
                $hashtag = Hashtag::firstOrCreate(
                    ['name' => $genreName],
                    ['type' => 'genre']
                );
                $hashtagIds[] = $hashtag->hashtag_id;
            }
            $movie->hashtags()->sync($hashtagIds);
            
            $this->command->info("Seeded: " . $data['title']);
        }
    }
}
