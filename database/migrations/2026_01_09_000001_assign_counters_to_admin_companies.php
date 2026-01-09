<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Company;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration fixes counters that don't have a company_id assigned.
     * It assigns them to a default company or creates one if needed.
     */
    public function up(): void
    {
        // Get all counters without a company_id
        $countersWithoutCompany = User::where('role', 'counter')
            ->whereNull('company_id')
            ->get();

        if ($countersWithoutCompany->count() > 0) {
            // Get or create a default company for orphaned counters
            $defaultCompany = Company::where('company_code', 'DEFAULT')->first();
            
            if (!$defaultCompany) {
                $defaultCompany = Company::create([
                    'company_code' => 'DEFAULT',
                    'company_name' => 'Default Company',
                    'is_active' => true,
                ]);
            }

            // Assign all orphaned counters to the default company
            User::where('role', 'counter')
                ->whereNull('company_id')
                ->update(['company_id' => $defaultCompany->id]);
        }

        // Also ensure all counters have a company_id set
        // If a counter has company_id but it references a deleted company, reassign to default
        $defaultCompany = Company::where('company_code', 'DEFAULT')->first() ?? 
                         Company::first();

        if ($defaultCompany) {
            User::where('role', 'counter')
                ->whereNotNull('company_id')
                ->whereNotIn('company_id', Company::pluck('id'))
                ->update(['company_id' => $defaultCompany->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove default company if it was created by this migration
        Company::where('company_code', 'DEFAULT')->delete();
    }
};
