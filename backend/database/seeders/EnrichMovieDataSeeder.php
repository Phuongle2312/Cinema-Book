<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Movie;
use App\Models\Cast;

class EnrichMovieDataSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 1. DEFINE CAST MEMBERS (Real & Sample)
        $actors = [
            'Timothée Chalamet',
            'Zendaya',
            'Rebecca Ferguson', // Dune
            'Tom Cruise',
            'Miles Teller', // Top Gun
            'Cillian Murphy',
            'Robert Downey Jr.', // Oppenheimer
            'Margot Robbie',
            'Ryan Gosling', // Barbie
            'Keanu Reeves',
            'Donnie Yen', // John Wick
            'Tran Thanh',
            'Tuan Tran', // Mai/Bo Gia
            'Kaity Nguyen',
            'Thai Hoa', // Last Wife
            'Avin Lu',
            'Hoang Ha', // Once upon a love story
            'Dieu Nhi',
            'Kieu Minh Tuan', // Gap lai chi bau
        ];

        $directors = [
            'Denis Villeneuve',
            'Christopher Nolan',
            'Greta Gerwig',
            'Chad Stahelski',
            'Tran Thanh',
            'Victor Vu',
            'Nhat Linh',
        ];

        $castIds = [];

        // Create Cast records
        foreach (array_merge($actors, $directors) as $name) {
            $cast = Cast::firstOrCreate(
                ['name' => $name],
                [
                    'bio' => "$name is a renowned artist in the film industry, known for their exceptional talent and dedication to cinema.",
                    'type' => 'both', // Generic type
                    'avatar' => "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=random",
                ]
            );
            $castIds[$name] = $cast->cast_id;
        }
        $this->command->info('Seeded Cast Members.');


        // 2. ENRICH MOVIES (Descriptions & Cast)
        $moviesData = [
            [
                'match' => 'Chainsaw Man',
                'description' => 'Denji is a teenage boy living with a Chainsaw Devil named Pochita. Due to the debt his father left behind, he has been living a rock-bottom life while repaying his debt by harvesting devil corpses with Pochita.',
                'cast' => ['Timothée Chalamet', 'Zendaya'],
                'director' => 'Christopher Nolan',
            ],
            [
                'match' => 'Mai',
                'description' => 'A touching story about Mai, a nearly 40-year-old single mother who constantly struggles with life\'s prejudices. When Duong - a young neighborhood musician - persistently pursues her, Mai finds herself hesitant to accept happiness.',
                'cast' => ['Tran Thanh', 'Tuan Tran'],
                'director' => 'Tran Thanh',
            ],
            [
                'match' => 'Dune',
                'description' => 'Paul Atreides, a brilliant and gifted young man born into a great destiny beyond his understanding, must travel to the most dangerous planet in the universe to ensure the future of his family and his people.',
                'cast' => ['Timothée Chalamet', 'Rebecca Ferguson', 'Zendaya'],
                'director' => 'Denis Villeneuve',
            ],
            [
                'match' => 'Oppenheimer',
                'description' => 'The story of American scientist J. Robert Oppenheimer and his role in the development of the atomic bomb.',
                'cast' => ['Cillian Murphy', 'Robert Downey Jr.'],
                'director' => 'Christopher Nolan',
            ],
            [
                'match' => 'The Rescue', // Cuu
                'description' => 'A high-stakes thriller involving a rescue mission that goes terribly wrong when the team encounters a mysterious entity.',
                'cast' => ['Tom Cruise', 'Miles Teller'],
                'director' => 'Christopher Nolan',
            ],
            [
                'match' => 'Face Off', // Lat Mat
                'description' => 'A grippy action film featuring intense combat sequences and a plot full of twists and turns involving hidden identities.',
                'cast' => ['Kieu Minh Tuan'],
                'director' => 'Ly Hai', // Just mock valid
            ],
            [
                'match' => 'Dad, I\'m Sorry', // Bo Gia
                'description' => 'A heartfelt family drama exploring the generational gap between a father and his son in a working-class neighborhood in Saigon.',
                'cast' => ['Tran Thanh', 'Tuan Tran'],
                'director' => 'Tran Thanh',
            ],
            [
                'match' => 'The Haunted Guild', // Nha Tran Quy
                'description' => 'A group of exorcists must band together to cleanse a cursed guild hall, but they soon realize the spirits are not the only danger lurking effectively.',
                'cast' => ['Kaity Nguyen', 'Thai Hoa'],
                'director' => 'Victor Vu',
            ],
        ];

        foreach ($moviesData as $data) {
            $movie = Movie::where('title', 'LIKE', '%' . $data['match'] . '%')->first();

            if ($movie) {
                // Update Description
                $movie->update(['description' => $data['description']]);

                // Attach Director
                if (isset($data['director']) && isset($castIds[$data['director']])) {
                    DB::table('movie_cast')->updateOrInsert(
                        ['movie_id' => $movie->movie_id, 'cast_id' => $castIds[$data['director']], 'role' => 'director'],
                        ['character_name' => 'Director']
                    );
                } else if (isset($data['director'])) {
                    // Create on fly if missed in top list
                    $d = Cast::firstOrCreate(['name' => $data['director']]);
                    DB::table('movie_cast')->updateOrInsert(
                        ['movie_id' => $movie->movie_id, 'cast_id' => $d->cast_id, 'role' => 'director'],
                        ['character_name' => 'Director']
                    );
                }

                // Attach Actors
                foreach ($data['cast'] as $actorName) {
                    $actorId = $castIds[$actorName] ?? null;
                    if (!$actorId) {
                        $a = Cast::firstOrCreate(['name' => $actorName]);
                        $actorId = $a->cast_id;
                    }

                    DB::table('movie_cast')->updateOrInsert(
                        ['movie_id' => $movie->movie_id, 'cast_id' => $actorId, 'role' => 'actor'],
                        ['character_name' => 'Lead Role']
                    );
                }

                $this->command->info("Enriched Movie: " . $movie->title);
            }
        }

        // Enrich ALL other movies with generic generic English description if short/Vietnamese
        $allMovies = Movie::all();
        foreach ($allMovies as $m) {
            if (str_word_count($m->description) < 5 || preg_match('/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/', $m->description)) {
                $m->update(['description' => 'Experience the cinematic masterpiece of the year. A story of love, betrayal, and redemption that will keep you on the edge of your seat.']);

                // Add random cast
                $randomCast = Cast::inRandomOrder()->take(2)->get();
                foreach ($randomCast as $c) {
                    DB::table('movie_cast')->updateOrInsert(
                        ['movie_id' => $m->movie_id, 'cast_id' => $c->cast_id, 'role' => 'actor'],
                        ['character_name' => 'Supporting Role']
                    );
                }
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->command->info('Enriched all movies with Cast and English descriptions.');
    }
}
