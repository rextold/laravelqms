<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed superadmin user first
        $this->call(SuperAdminSeeder::class);

        // Seed organizations with their admins and counters
        $this->call(OrganizationSeeder::class);
    }
}
