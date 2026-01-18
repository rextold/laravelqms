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
        // Migration disabled: application uses `sync` queue driver.
        // Prevent creating the `jobs` table to avoid writing job payloads to the database.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op rollback since table creation is disabled.
    }
};
