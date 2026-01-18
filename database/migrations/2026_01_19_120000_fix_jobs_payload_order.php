<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Move payload to the end of the columns so inserts from Laravel map correctly.
        // Some MySQL versions require NOT NULL for MODIFY; payload is longText and not null in existing schema.
        DB::statement('ALTER TABLE `jobs` MODIFY `payload` LONGTEXT NOT NULL AFTER `created_at`');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Move payload back to after queue (original position)
        DB::statement('ALTER TABLE `jobs` MODIFY `payload` LONGTEXT NOT NULL AFTER `queue`');
    }
};
