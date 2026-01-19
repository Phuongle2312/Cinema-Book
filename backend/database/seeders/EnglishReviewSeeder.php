<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Movie;
use App\Models\Review;
use Faker\Factory as Faker;

class EnglishReviewSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('en_US');

        $movies = Movie::where('status', 'now_showing')->pluck('movie_id')->toArray();
        // Fallback if no now_showing
        if (empty($movies)) {
            $movies = Movie::pluck('movie_id')->toArray();
        }

        // Get users we just created (assuming ids > some number, or just random)
        $users = User::pluck('id')->toArray();

        $comments = [
            "Absolutely fantastic! The visual effects were cutting edge.",
            "A bit slow in the beginning, but the ending was totally worth it.",
            "Best movie I've seen in years. Highly recommend watching in IMAX.",
            "Great acting, but the plot had some holes.",
            "Solid 8/10. Good popcorn flick for the weekend.",
            "I cried at the end. Such an emotional rollercoaster.",
            "The sound design was impeccable. A true cinematic experience.",
            "Not what I expected, but pleasantly surprised.",
            "A masterpiece of modern storytelling.",
            "Just okay. I wouldn't watch it twice."
        ];

        foreach ($movies as $movieId) {
            // Add 3-5 English reviews per movie
            for ($i = 0; $i < rand(3, 5); $i++) {
                $userId = $users[array_rand($users)];

                // Check unique
                if (Review::where('user_id', $userId)->where('movie_id', $movieId)->exists()) {
                    continue;
                }

                Review::create([
                    'user_id' => $userId,
                    'movie_id' => $movieId,
                    'rating' => rand(3, 5),
                    'comment' => $faker->randomElement($comments),
                    'is_approved' => true,
                    'is_verified_purchase' => (rand(0, 1) == 1),
                    'created_at' => $faker->dateTimeBetween('-1 month', 'now'),
                ]);
            }
        }
        $this->command->info('Seeded English reviews successfully!');
    }
}
