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
        Schema::table('organization_settings', function (Blueprint $table) {
            // Rename company columns to organization columns
            if (Schema::hasColumn('organization_settings', 'company_logo')) {
                $table->renameColumn('company_logo', 'organization_logo');
            }
            if (Schema::hasColumn('organization_settings', 'company_phone')) {
                $table->renameColumn('company_phone', 'organization_phone');
            }
            if (Schema::hasColumn('organization_settings', 'company_email')) {
                $table->renameColumn('company_email', 'organization_email');
            }
            if (Schema::hasColumn('organization_settings', 'company_address')) {
                $table->renameColumn('company_address', 'organization_address');
            }
            if (Schema::hasColumn('organization_settings', 'company_name')) {
                $table->renameColumn('company_name', 'organization_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            // Rename organization columns back to company columns
            if (Schema::hasColumn('organization_settings', 'organization_logo')) {
                $table->renameColumn('organization_logo', 'company_logo');
            }
            if (Schema::hasColumn('organization_settings', 'organization_phone')) {
                $table->renameColumn('organization_phone', 'company_phone');
            }
            if (Schema::hasColumn('organization_settings', 'organization_email')) {
                $table->renameColumn('organization_email', 'company_email');
            }
            if (Schema::hasColumn('organization_settings', 'organization_address')) {
                $table->renameColumn('organization_address', 'company_address');
            }
            if (Schema::hasColumn('organization_settings', 'organization_name')) {
                $table->renameColumn('organization_name', 'company_name');
            }
        });
    }
};
