<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('video_controls')) return;

        Schema::table('video_controls', function (Blueprint $table) {
            if (Schema::hasColumn('video_controls', 'repeat_mode')) {
                $table->dropColumn('repeat_mode');
            }
            if (Schema::hasColumn('video_controls', 'is_shuffle')) {
                $table->dropColumn('is_shuffle');
            }
            if (Schema::hasColumn('video_controls', 'is_sequence')) {
                $table->dropColumn('is_sequence');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('video_controls')) return;

        Schema::table('video_controls', function (Blueprint $table) {
            if (!Schema::hasColumn('video_controls', 'repeat_mode')) {
                $table->enum('repeat_mode', ['off', 'one', 'all'])->default('all')->after('current_video_id');
            }
            if (!Schema::hasColumn('video_controls', 'is_shuffle')) {
                $table->boolean('is_shuffle')->default(false)->after('repeat_mode');
            }
            if (!Schema::hasColumn('video_controls', 'is_sequence')) {
                $table->boolean('is_sequence')->default(true)->after('is_shuffle');
            }
        });
    }
};
