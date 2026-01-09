<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'priority_code')) {
                $table->string('priority_code')->nullable()->after('counter_number');
                $table->index('priority_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'priority_code')) {
                $table->dropIndex(['priority_code']);
                $table->dropColumn('priority_code');
            }
        });
    }
};
