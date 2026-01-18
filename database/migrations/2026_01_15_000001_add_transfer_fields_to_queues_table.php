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
        Schema::table('queues', function (Blueprint $table) {
            if (!Schema::hasColumn('queues', 'transferred_from')) {
                $table->unsignedBigInteger('transferred_from')->nullable()->after('transferred_to');
            }
            if (!Schema::hasColumn('queues', 'transferred_at')) {
                $table->timestamp('transferred_at')->nullable()->after('transferred_from');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            if (Schema::hasColumn('queues', 'transferred_from')) {
                $table->dropColumn('transferred_from');
            }
            if (Schema::hasColumn('queues', 'transferred_at')) {
                $table->dropColumn('transferred_at');
            }
        });
    }
};