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

        // Get all companies - refresh from database to ensure we have correct IDs
        $companies = Company::all();
        $defaultCompany = Company::where('company_code', 'DEFAULT')->first();

        if (!$defaultCompany) {
            throw new \Exception('DEFAULT company not found. Please run CompanySeeder first.');
        }

        // Create SuperAdmin (no company_id - supervises all companies)
        User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'email' => 'superadmin@qms.com',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'company_id' => null,
            ]
        );

        // Create Admin for DEFAULT company with username 'admin'
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@qms.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'company_id' => $defaultCompany->id,
            ]
        );

        // Create admins for other companies with unique usernames
        foreach ($companies as $company) {
            if ($company->id === $defaultCompany->id) {
                continue; // Skip DEFAULT, already created
            }

            $username = 'admin_' . strtolower($company->company_code);
            User::firstOrCreate(
                ['username' => $username],
                [
                    'email' => 'admin@' . strtolower($company->company_code) . '.com',
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'company_id' => $company->id,
                ]
            );
        }

        // Create sample counters for default company
        for ($i = 1; $i <= 5; $i++) {
            User::firstOrCreate(
                ['username' => 'counter' . $i],
                [
                    'password' => Hash::make('password'),
                    'role' => 'counter',
                    'display_name' => 'Counter ' . $i,
                    'counter_number' => $i,
                    'short_description' => 'General Service Counter',
                    'is_online' => false,
                    'company_id' => $defaultCompany->id,
                ]
            );
        }
    }
}
