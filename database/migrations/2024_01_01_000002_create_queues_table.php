<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->string('queue_number')->unique();
            $table->foreignId('counter_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['waiting', 'called', 'serving', 'completed', 'transferred'])->default('waiting');
            $table->foreignId('transferred_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
