<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed companies first
        $this->call(CompanySeeder::class);

        // Get the default company
        $company = Company::where('company_code', 'DEFAULT')->first();

        // Create SuperAdmin (no company_id - supervises all companies)
        User::create([
            'username' => 'superadmin',
            'email' => 'superadmin@qms.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'company_id' => null,
        ]);

        // Create Admin
        User::create([
            'username' => 'admin',
            'email' => 'admin@qms.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'company_id' => $company->id,
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
                'company_id' => $company->id,
            ]);
        }
    }
}
