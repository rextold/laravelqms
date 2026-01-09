<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\CompanySetting;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default company if not exists
        $defaultCompany = Company::firstOrCreate(
            ['company_code' => 'DEFAULT'],
            [
                'company_name' => 'Default Company',
                'primary_color' => '#3b82f6',
                'secondary_color' => '#8b5cf6',
                'accent_color' => '#10b981',
                'text_color' => '#ffffff',
                'queue_number_digits' => 4,
                'is_active' => true,
            ]
        );

        // Create company settings for default company
        CompanySetting::firstOrCreate(
            ['company_id' => $defaultCompany->id],
            [
                'code' => $defaultCompany->company_code,
                'company_name' => $defaultCompany->company_name,
                'primary_color' => $defaultCompany->primary_color,
                'secondary_color' => $defaultCompany->secondary_color,
                'accent_color' => $defaultCompany->accent_color,
                'text_color' => $defaultCompany->text_color,
                'queue_number_digits' => $defaultCompany->queue_number_digits,
                'is_active' => true,
            ]
        );

        // Create additional sample companies
        $companyA = Company::firstOrCreate(
            ['company_code' => 'COMPANY_A'],
            [
                'company_name' => 'Company A - Branch 1',
                'primary_color' => '#ef4444',
                'secondary_color' => '#f97316',
                'accent_color' => '#fbbf24',
                'text_color' => '#ffffff',
                'queue_number_digits' => 4,
                'is_active' => true,
            ]
        );

        CompanySetting::firstOrCreate(
            ['company_id' => $companyA->id],
            [
                'code' => $companyA->company_code,
                'company_name' => $companyA->company_name,
                'primary_color' => $companyA->primary_color,
                'secondary_color' => $companyA->secondary_color,
                'accent_color' => $companyA->accent_color,
                'text_color' => $companyA->text_color,
                'queue_number_digits' => $companyA->queue_number_digits,
                'is_active' => true,
            ]
        );

        $companyB = Company::firstOrCreate(
            ['company_code' => 'COMPANY_B'],
            [
                'company_name' => 'Company B - Branch 2',
                'primary_color' => '#06b6d4',
                'secondary_color' => '#0891b2',
                'accent_color' => '#14b8a6',
                'text_color' => '#ffffff',
                'queue_number_digits' => 4,
                'is_active' => true,
            ]
        );

        CompanySetting::firstOrCreate(
            ['company_id' => $companyB->id],
            [
                'code' => $companyB->company_code,
                'company_name' => $companyB->company_name,
                'primary_color' => $companyB->primary_color,
                'secondary_color' => $companyB->secondary_color,
                'accent_color' => $companyB->accent_color,
                'text_color' => $companyB->text_color,
                'queue_number_digits' => $companyB->queue_number_digits,
                'is_active' => true,
            ]
        );
    }
}
