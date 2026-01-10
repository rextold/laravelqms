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
        if (Schema::hasTable('marquee_settings')) {
            Schema::table('marquee_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('marquee_settings', 'organization_id')) {
                    $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                    $table->foreign('organization_id')
                        ->references('id')
                        ->on('organizations')
                        ->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('marquee_settings')) {
            Schema::table('marquee_settings', function (Blueprint $table) {
                if (Schema::hasColumn('marquee_settings', 'organization_id')) {
                    $table->dropForeign(['organization_id']);
                    $table->dropColumn('organization_id');
                }
            });
        }
    }
};
