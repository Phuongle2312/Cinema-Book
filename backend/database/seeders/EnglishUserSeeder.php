<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class EnglishUserSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('en_US'); // Force English locale
        $password = Hash::make('123456');

        $users = [];
        for ($i = 0; $i < 20; $i++) {
            $name = $faker->name;
            $email = $faker->unique()->safeEmail;

            // Shorter avatar URL to avoid truncation
            $avatar = "https://ui-avatars.com/api/?name=" . substr(urlencode($name), 0, 5);

            $users[] = [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'phone' => substr($faker->phoneNumber, 0, 15),
                'role' => 'user',
                'avatar' => $avatar,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        User::insert($users);
        $this->command->info('Seeded 20 English users successfully!');
    }
}
