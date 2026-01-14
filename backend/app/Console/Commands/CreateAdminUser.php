<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {--email=admin@cinebook.com} {--password=admin123} {--name=Admin}';
    protected $description = 'Create an admin user account';

    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');

        // Check if user exists
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            if ($existingUser->role === 'admin') {
                $this->info("Admin user already exists: {$email}");
                return 0;
            }

            // Update existing user to admin
            $existingUser->update([
                'role' => 'admin',
                'password' => Hash::make($password),
            ]);
            $this->info("Updated existing user to admin: {$email}");
            return 0;
        }

        // Create new admin user
        $admin = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
        ]);

        $this->info("Admin user created successfully!");
        $this->table(
            ['Field', 'Value'],
            [
                ['Email', $admin->email],
                ['Name', $admin->name],
                ['Password', $password],
                ['Role', $admin->role],
            ]
        );

        return 0;
    }
}
