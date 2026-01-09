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
        Schema::table('video_controls', function (Blueprint $table) {
            $table->string('bell_sound_path')->nullable()->after('bell_volume');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_controls', function (Blueprint $table) {
            $table->dropColumn('bell_sound_path');
        });
    }
};
