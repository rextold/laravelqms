<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            // Tracks which calendar date the current last_queue_sequence belongs to.
            // When the date changes, QueueService resets the sequence to 0 so the
            // first ticket of the new day starts at 0001.
            $table->date('last_queue_date')->nullable()->after('last_queue_sequence');
        });
    }

    public function down(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            $table->dropColumn('last_queue_date');
        });
    }
};
