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
        Schema::table('organization_settings', function (Blueprint $table) {
            // Notification display messages
            if (!Schema::hasColumn('organization_settings', 'notify_title')) {
                $table->string('notify_title', 255)->default('Now Calling')->after('queue_number_digits');
            }
            if (!Schema::hasColumn('organization_settings', 'notify_message')) {
                $table->string('notify_message', 500)->default('Please proceed to the counter')->after('notify_title');
            }
            if (!Schema::hasColumn('organization_settings', 'serve_title')) {
                $table->string('serve_title', 255)->default('Now Serving')->after('notify_message');
            }
            if (!Schema::hasColumn('organization_settings', 'serve_message')) {
                $table->string('serve_message', 500)->default('Please proceed to Counter {counter}')->after('serve_title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            $columns = ['notify_title', 'notify_message', 'serve_title', 'serve_message'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('organization_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};