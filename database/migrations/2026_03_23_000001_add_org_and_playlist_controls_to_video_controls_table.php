<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_controls', function (Blueprint $table) {
            // Multi-tenant: each organisation has its own video control state
            if (!Schema::hasColumn('video_controls', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')
                      ->references('id')->on('organizations')
                      ->onDelete('cascade');
            }

            // Playlist playback controls
            if (!Schema::hasColumn('video_controls', 'repeat_mode')) {
                $table->string('repeat_mode')->default('none')->after('current_video_id');
            }
            if (!Schema::hasColumn('video_controls', 'is_shuffle')) {
                $table->boolean('is_shuffle')->default(false)->after('repeat_mode');
            }
            if (!Schema::hasColumn('video_controls', 'is_sequence')) {
                $table->boolean('is_sequence')->default(true)->after('is_shuffle');
            }
        });

        // Assign any existing (organisation-less) rows to the first organisation so
        // existing video-control settings are not lost after this migration.
        $firstOrg = DB::table('organizations')->orderBy('id')->first();
        if ($firstOrg) {
            DB::table('video_controls')
                ->whereNull('organization_id')
                ->update(['organization_id' => $firstOrg->id]);
        }
    }

    public function down(): void
    {
        Schema::table('video_controls', function (Blueprint $table) {
            if (Schema::hasColumn('video_controls', 'organization_id')) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            }
            foreach (['repeat_mode', 'is_shuffle', 'is_sequence'] as $col) {
                if (Schema::hasColumn('video_controls', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
