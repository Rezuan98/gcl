<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'), // Default password
                'email_verified_at' => now(),
            ]
        );

        // Optional: create demo user
        User::updateOrCreate(
            ['email' => 'admin@gcl.com'],
            [
                'name' => 'gcl admin',
                'password' => Hash::make('gcl@admin'),
                'email_verified_at' => now(),
            ]
        );
    }
}
