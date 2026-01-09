<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

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
        // Skip if organizations table doesn't exist yet
        if (!Schema::hasTable('organizations')) {
            return;
        }

        // Get all counters without an organization_id
        $countersWithoutOrganization = User::where('role', 'counter')
            ->whereNull('organization_id')
            ->get();

        if ($countersWithoutOrganization->count() > 0) {
            // For fresh migrations, organizations table won't have entries yet
            // Just skip this step if there are no companies
            DB::table('companies')->count() > 0 ? true : false;
            
            $defaultCompanyId = DB::table('companies')
                ->where('organization_code', 'DEFAULT')
                ->value('id');
            
            if ($defaultCompanyId) {
                // Assign all orphaned counters to the default company
                User::where('role', 'counter')
                    ->whereNull('company_id')
                    ->update(['company_id' => $defaultCompanyId]);
            }
        }

        // Also ensure all counters have an organization_id set
        $defaultOrganizationId = DB::table('organizations')
            ->where('organization_code', 'DEFAULT')
            ->value('id');
        
        if ($defaultOrganizationId) {
            User::where('role', 'counter')
                ->whereNotNull('organization_id')
                ->whereNotIn('organization_id', DB::table('organizations')->pluck('id'))
                ->update(['organization_id' => $defaultOrganizationId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration doesn't delete data on rollback
    }
};

