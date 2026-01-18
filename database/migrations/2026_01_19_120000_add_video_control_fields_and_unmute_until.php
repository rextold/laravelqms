<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('video_controls')) {
            Schema::table('video_controls', function (Blueprint $table) {
                if (!Schema::hasColumn('video_controls', 'bell_choice')) {
                    $table->string('bell_choice')->nullable()->after('bell_sound_path');
                }
                if (!Schema::hasColumn('video_controls', 'video_muted')) {
                    $table->boolean('video_muted')->default(true)->after('bell_choice');
                }
                if (!Schema::hasColumn('video_controls', 'autoplay')) {
                    $table->boolean('autoplay')->default(false)->after('video_muted');
                }
                if (!Schema::hasColumn('video_controls', 'loop')) {
                    $table->boolean('loop')->default(true)->after('autoplay');
                }
                if (!Schema::hasColumn('video_controls', 'unmute_until')) {
                    $table->timestamp('unmute_until')->nullable()->after('loop');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('video_controls')) {
            Schema::table('video_controls', function (Blueprint $table) {
                if (Schema::hasColumn('video_controls', 'bell_choice')) {
                    $table->dropColumn('bell_choice');
                }
                if (Schema::hasColumn('video_controls', 'video_muted')) {
                    $table->dropColumn('video_muted');
                }
                if (Schema::hasColumn('video_controls', 'autoplay')) {
                    $table->dropColumn('autoplay');
                }
                if (Schema::hasColumn('video_controls', 'loop')) {
                    $table->dropColumn('loop');
                }
                if (Schema::hasColumn('video_controls', 'unmute_until')) {
                    $table->dropColumn('unmute_until');
                }
            });
        }
    }
};
