<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed an initial administrator account from environment variables.
     *
     * Configure INITIAL_ADMIN_NAME, INITIAL_ADMIN_EMAIL and
     * INITIAL_ADMIN_PASSWORD in your .env file before running this seeder.
     * For interactive creation, prefer: php artisan app:create-admin
     */
    public function run(): void
    {
        $email = env('INITIAL_ADMIN_EMAIL');
        $password = env('INITIAL_ADMIN_PASSWORD');
        $name = env('INITIAL_ADMIN_NAME', 'Administrator');

        if (! $email || ! $password) {
            $this->command?->warn(
                'Skipping AdminSeeder: set INITIAL_ADMIN_EMAIL and INITIAL_ADMIN_PASSWORD in .env to seed an admin.'
            );

            return;
        }

        if (User::where('email', $email)->exists()) {
            $this->command?->warn("Skipping AdminSeeder: a user with email {$email} already exists.");

            return;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => Role::Admin,
        ]);

        $this->command?->info("Administrator account seeded: {$email}");
    }
}
