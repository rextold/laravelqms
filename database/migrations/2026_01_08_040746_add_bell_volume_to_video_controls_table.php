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
            $table->integer('bell_volume')->default(100)->after('volume');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_controls', function (Blueprint $table) {
            $table->dropColumn('bell_volume');
        });
    }
};
