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
        // Add playlist control fields to video_controls table
        if (Schema::hasTable('video_controls')) {
            Schema::table('video_controls', function (Blueprint $table) {
                if (!Schema::hasColumn('video_controls', 'current_video_id')) {
                    $table->unsignedBigInteger('current_video_id')->nullable()->after('volume');
                }
                if (!Schema::hasColumn('video_controls', 'repeat_mode')) {
                    $table->enum('repeat_mode', ['off', 'one', 'all'])->default('all')->after('current_video_id');
                }
                if (!Schema::hasColumn('video_controls', 'is_shuffle')) {
                    $table->boolean('is_shuffle')->default(false)->after('repeat_mode');
                }
                if (!Schema::hasColumn('video_controls', 'is_sequence')) {
                    $table->boolean('is_sequence')->default(true)->after('is_shuffle');
                }
                if (!Schema::hasColumn('video_controls', 'organization_id')) {
                    $table->unsignedBigInteger('organization_id')->nullable()->after('is_sequence');
                }
            });
        }

        // Create playlist_items table for managing video sequences
        Schema::create('playlist_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('video_id');
            $table->unsignedBigInteger('organization_id');
            $table->integer('sequence_order')->default(0);
            $table->timestamps();

            $table->foreign('video_id')->references('id')->on('videos')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->unique(['video_id', 'organization_id']);
            $table->index(['organization_id', 'sequence_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlist_items');
        
        if (Schema::hasTable('video_controls')) {
            Schema::table('video_controls', function (Blueprint $table) {
                $table->dropColumnIfExists('repeat_mode');
                $table->dropColumnIfExists('is_shuffle');
                $table->dropColumnIfExists('is_sequence');
                $table->dropColumnIfExists('organization_id');
            });
        }
    }
};
