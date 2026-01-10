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
        if (Schema::hasTable('organization_settings')) {
            Schema::table('organization_settings', function (Blueprint $table) {
                // Add columns if they don't exist
                if (!Schema::hasColumn('organization_settings', 'phone')) {
                    $table->string('phone')->nullable()->after('logo_path');
                }
                if (!Schema::hasColumn('organization_settings', 'email')) {
                    $table->string('email')->nullable()->after('phone');
                }
                if (!Schema::hasColumn('organization_settings', 'address')) {
                    $table->text('address')->nullable()->after('email');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('organization_settings')) {
            Schema::table('organization_settings', function (Blueprint $table) {
                $table->dropColumn(['phone', 'email', 'address']);
            });
        }
    }
};
