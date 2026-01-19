<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Review;
use App\Models\Movie;
use App\Models\User;
use Faker\Factory as Faker;

class ReviewSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Ensure we have some users
        if (User::count() < 10) {
            User::factory()->count(10)->create();
        }

        $users = User::pluck('id')->toArray();
        $movies = Movie::where('status', 'now_showing')->pluck('movie_id')->toArray();

        if (empty($movies)) {
            $this->command->info('No "now_showing" movies found. Seeding reviews for random movies instead.');
            $movies = Movie::pluck('movie_id')->toArray();
        }

        foreach ($movies as $movieId) {
            // Add 3-6 reviews per movie (reduced to avoid running out of users)
            $numReviews = rand(3, 6);

            // Shuffle users to avoid picking same user for same movie multiple times
            $shuffledUsers = $users;
            shuffle($shuffledUsers);

            for ($i = 0; $i < $numReviews; $i++) {
                if (empty($shuffledUsers))
                    break;

                $userId = array_pop($shuffledUsers);

                // Check if review already exists
                $exists = Review::where('user_id', $userId)->where('movie_id', $movieId)->exists();

                if (!$exists) {
                    Review::create([
                        'user_id' => $userId,
                        'movie_id' => $movieId,
                        'rating' => $faker->numberBetween(3, 5), // Mostly positive
                        'comment' => $faker->randomElement([
                            "Movie was amazing! Highly recommended.",
                            "Great visuals but the story was a bit weak.",
                            "Loved the acting, especially the main character.",
                            "Solid 4/5. Would watch again.",
                            "Not my cup of tea, but production value is high.",
                            "Best movie I've seen this year!",
                            "The cinema experience was great.",
                            "A bit too long for my taste.",
                            "Masterpiece!"
                        ]),
                        'created_at' => $faker->dateTimeBetween('-1 month', 'now'),
                    ]);
                }
            }
        }

        $this->command->info('seeded reviews successfully!');
    }
}
