<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->timestamp('notified_at')->nullable()->after('called_at');
            $table->timestamp('skipped_at')->nullable()->after('notified_at');
        });
        
        // Update enum to include 'skipped' status
        DB::statement("ALTER TABLE queues MODIFY COLUMN status ENUM('waiting', 'called', 'serving', 'completed', 'transferred', 'skipped') DEFAULT 'waiting'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn(['notified_at', 'skipped_at']);
        });
        
        // Revert enum back
        DB::statement("ALTER TABLE queues MODIFY COLUMN status ENUM('waiting', 'called', 'serving', 'completed', 'transferred') DEFAULT 'waiting'");
    }
};
