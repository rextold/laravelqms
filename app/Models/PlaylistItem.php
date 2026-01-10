<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaylistItem extends Model
{
    use HasFactory;

    protected $table = 'playlist_items';

    protected $fillable = [
        'video_id',
        'organization_id',
        'sequence_order',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get playlist for an organization
     */
    public static function getPlaylist($organizationId)
    {
        return self::where('organization_id', $organizationId)
            ->orderBy('sequence_order')
            ->with('video')
            ->get();
    }

    /**
     * Reorder playlist items
     */
    public static function reorderPlaylist($organizationId, array $videoIds)
    {
        foreach ($videoIds as $index => $videoId) {
            self::where('video_id', $videoId)
                ->where('organization_id', $organizationId)
                ->update(['sequence_order' => $index]);
        }
    }

    /**
     * Add video to playlist
     */
    public static function addToPlaylist($organizationId, $videoId)
    {
        $maxOrder = self::where('organization_id', $organizationId)->max('sequence_order') ?? -1;
        
        return self::create([
            'video_id' => $videoId,
            'organization_id' => $organizationId,
            'sequence_order' => $maxOrder + 1,
        ]);
    }

    /**
     * Remove video from playlist
     */
    public static function removeFromPlaylist($organizationId, $videoId)
    {
        return self::where('video_id', $videoId)
            ->where('organization_id', $organizationId)
            ->delete();
    }

    /**
     * Get next video in sequence
     */
    public static function getNextVideo($organizationId, $currentVideoId)
    {
        $current = self::where('video_id', $currentVideoId)
            ->where('organization_id', $organizationId)
            ->first();

        if (!$current) {
            return self::where('organization_id', $organizationId)
                ->orderBy('sequence_order')
                ->first();
        }

        return self::where('organization_id', $organizationId)
            ->where('sequence_order', '>', $current->sequence_order)
            ->orderBy('sequence_order')
            ->first();
    }

    /**
     * Get previous video in sequence
     */
    public static function getPreviousVideo($organizationId, $currentVideoId)
    {
        $current = self::where('video_id', $currentVideoId)
            ->where('organization_id', $organizationId)
            ->first();

        if (!$current) {
            return self::where('organization_id', $organizationId)
                ->orderBy('sequence_order', 'desc')
                ->first();
        }

        return self::where('organization_id', $organizationId)
            ->where('sequence_order', '<', $current->sequence_order)
            ->orderBy('sequence_order', 'desc')
            ->first();
    }
}
