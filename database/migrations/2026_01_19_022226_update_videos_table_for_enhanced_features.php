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
        Schema::table('videos', function (Blueprint $table) {
            // Add missing columns that are referenced in the Video model but not in the original migration
            if (!Schema::hasColumn('videos', 'video_type')) {
                $table->enum('video_type', ['file', 'youtube'])->default('file')->after('title');
            }
            
            if (!Schema::hasColumn('videos', 'youtube_url')) {
                $table->text('youtube_url')->nullable()->after('file_path');
            }
            
            if (!Schema::hasColumn('videos', 'organization_id')) {
                $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade')->after('id');
            }
            
            // Enhanced video metadata
            if (!Schema::hasColumn('videos', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            
            if (!Schema::hasColumn('videos', 'duration')) {
                $table->integer('duration')->nullable()->comment('Duration in seconds')->after('youtube_url');
            }
            
            if (!Schema::hasColumn('videos', 'thumbnail_path')) {
                $table->string('thumbnail_path')->nullable()->after('duration');
            }
            
            // Playlist and scheduling features
            if (Schema::hasTable('playlists') && !Schema::hasColumn('videos', 'playlist_id')) {
                $table->foreignId('playlist_id')->nullable()->constrained()->onDelete('set null')->after('organization_id');
            }            
            if (!Schema::hasColumn('videos', 'start_date')) {
                $table->date('start_date')->nullable()->after('is_active');
            }
            
            if (!Schema::hasColumn('videos', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            
            if (!Schema::hasColumn('videos', 'start_time')) {
                $table->time('start_time')->nullable()->after('end_date');
            }
            
            if (!Schema::hasColumn('videos', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }
            
            // Days of week (JSON array: [1,2,3,4,5] for Mon-Fri)
            if (!Schema::hasColumn('videos', 'days_of_week')) {
                $table->json('days_of_week')->nullable()->after('end_time');
            }
            
            // Video settings
            if (!Schema::hasColumn('videos', 'volume')) {
                $table->integer('volume')->default(50)->after('days_of_week');
            }
            
            if (!Schema::hasColumn('videos', 'auto_advance')) {
                $table->boolean('auto_advance')->default(true)->after('volume');
            }
            
            // Priority for scheduling conflicts
            if (!Schema::hasColumn('videos', 'priority')) {
                $table->integer('priority')->default(0)->after('auto_advance');
            }
            
            // Add indexes for better performance
            $table->index(['organization_id', 'is_active', 'order']);
            $table->index(['playlist_id', 'order']);
            $table->index(['video_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['organization_id', 'is_active', 'order']);
            $table->dropIndex(['playlist_id', 'order']);
            $table->dropIndex(['video_type', 'is_active']);
            
            // Drop columns in reverse order
            $table->dropColumn([
                'priority',
                'auto_advance',
                'volume',
                'days_of_week',
                'end_time',
                'start_time',
                'end_date',
                'start_date',
                'playlist_id',
                'thumbnail_path',
                'duration',
                'description',
                'organization_id',
                'youtube_url',
                'video_type'
            ]);
        });
    }
};
