<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create organizations
        $organizations = [
            [
                'organization_code' => 'DEFAULT',
                'organization_name' => 'Default Organization',
                'is_active' => true,
            ],
            [
                'organization_code' => 'ORG_A',
                'organization_name' => 'Organization A',
                'is_active' => true,
            ],
            [
                'organization_code' => 'ORG_B',
                'organization_name' => 'Organization B',
                'is_active' => true,
            ],
        ];

        foreach ($organizations as $orgData) {
            $organization = Organization::firstOrCreate(
                ['organization_code' => $orgData['organization_code']],
                $orgData
            );

            // Create organization settings
            OrganizationSetting::firstOrCreate(
                ['organization_id' => $organization->id],
                [
                    'code' => $organization->organization_code,
                    'primary_color' => '#4F46E5',
                    'secondary_color' => '#10B981',
                    'accent_color' => '#F59E0B',
                    'text_color' => '#1F2937',
                    'queue_number_digits' => 4,
                    'is_active' => true,
                ]
            );

            // Create admin user for each organization with unique username
            $adminUsername = $orgData['organization_code'] === 'DEFAULT' 
                ? 'admin' 
                : 'admin_' . strtolower($orgData['organization_code']);

            User::firstOrCreate(
                ['username' => $adminUsername],
                [
                    'email' => str_replace(' ', '_', strtolower($orgData['organization_name'])) . '_admin@example.com',
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'organization_id' => $organization->id,
                ]
            );

            // Create sample counter users for each organization
            for ($i = 1; $i <= 2; $i++) {
                User::firstOrCreate(
                    ['username' => 'counter_' . strtolower(str_replace(' ', '_', $orgData['organization_code'])) . '_' . $i],
                    [
                        'email' => str_replace(' ', '_', strtolower($orgData['organization_name'])) . '_counter_' . $i . '@example.com',
                        'password' => Hash::make('password'),
                        'role' => 'counter',
                        'organization_id' => $organization->id,
                        'display_name' => 'Counter ' . $i,
                        'counter_number' => $i,
                        'short_description' => 'Service Counter ' . $i,
                    ]
                );
            }
        }
    }
}
