<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('organization_settings')) {
            return;
        }

        if (Schema::hasColumn('organization_settings', 'company_logo') && !Schema::hasColumn('organization_settings', 'organization_logo')) {
            Schema::table('organization_settings', function (Blueprint $table) {
                $table->renameColumn('company_logo', 'organization_logo');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('organization_settings')) {
            return;
        }

        if (Schema::hasColumn('organization_settings', 'organization_logo') && !Schema::hasColumn('organization_settings', 'company_logo')) {
            Schema::table('organization_settings', function (Blueprint $table) {
                $table->renameColumn('organization_logo', 'company_logo');
            });
        }
    }
};
