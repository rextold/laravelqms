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
        Schema::table('company_settings', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('queue_number_digits');
            $table->string('code')->unique()->after('id'); // Unique company code
        });
        
        // Set default code for existing record
        DB::table('company_settings')->update(['code' => 'MAIN']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'code']);
        });
    }
};
