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
        Schema::create('display_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            
            // Display Mode Settings
            $table->enum('display_mode', ['fullscreen', 'windowed', 'split'])->default('fullscreen');
            $table->enum('video_fit', ['cover', 'contain', 'fill', 'scale-down'])->default('cover');
            $table->integer('auto_advance_time')->default(30); // seconds
            
            // UI Elements
            $table->boolean('show_queue_info')->default(true);
            $table->boolean('show_clock')->default(true);
            $table->boolean('show_date')->default(true);
            $table->boolean('show_weather')->default(false);
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_marquee')->default(true);
            
            // Visual Settings
            $table->string('background_color', 7)->default('#000000');
            $table->string('text_color', 7)->default('#ffffff');
            $table->string('accent_color', 7)->default('#3b82f6');
            $table->integer('font_size')->default(16);
            $table->string('font_family')->default('Inter');
            
            // Animation & Transitions
            $table->enum('transition_effect', ['fade', 'slide', 'zoom', 'none'])->default('fade');
            $table->integer('transition_duration')->default(1000); // milliseconds
            
            // Screen Saver
            $table->boolean('screen_saver_enabled')->default(true);
            $table->integer('screen_saver_timeout')->default(300); // seconds
            
            // Video Settings
            $table->integer('brightness')->default(100);
            $table->integer('contrast')->default(100);
            $table->boolean('volume_control')->default(true);
            $table->json('mute_during_hours')->nullable(); // Array of time ranges
            
            // Display Resolution
            $table->string('display_resolution')->default('1920x1080');
            $table->integer('refresh_rate')->default(60);
            
            // Playlist Settings
            $table->boolean('auto_play')->default(true);
            $table->boolean('loop_playlist')->default(true);
            $table->boolean('shuffle_videos')->default(false);
            $table->integer('video_duration_limit')->default(300); // seconds, 0 = no limit
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index(['organization_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('display_settings');
    }
};
