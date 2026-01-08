<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_controls', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_playing')->default(true);
            $table->integer('volume')->default(50);
            $table->foreignId('current_video_id')->nullable()->constrained('videos')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_controls');
    }
};
