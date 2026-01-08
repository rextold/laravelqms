<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create SuperAdmin
        User::create([
            'username' => 'superadmin',
            'email' => 'superadmin@qms.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);

        // Create Admin
        User::create([
            'username' => 'admin',
            'email' => 'admin@qms.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create sample counters
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'username' => 'counter' . $i,
                'password' => Hash::make('password'),
                'role' => 'counter',
                'display_name' => 'Counter ' . $i,
                'counter_number' => $i,
                'short_description' => 'General Service Counter',
                'is_online' => false,
            ]);
        }
    }
}
