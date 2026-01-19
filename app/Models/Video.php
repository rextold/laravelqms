<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'video_type',
        'file_path',
        'youtube_url',
        'duration',
        'thumbnail_path',
        'order',
        'is_active',
        'organization_id',
        'playlist_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'days_of_week',
        'volume',
        'auto_advance',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_advance' => 'boolean',
        'days_of_week' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'duration' => 'integer',
        'volume' => 'integer',
        'priority' => 'integer',
        'order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }

    /**
     * Get YouTube embed URL from various YouTube URL formats
     */
    public function getYoutubeEmbedUrlAttribute()
    {
        if ($this->video_type !== 'youtube' || !$this->youtube_url) {
            return null;
        }

        $url = $this->youtube_url;
        
        // Extract video ID from various YouTube URL formats
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
        
        $videoId = $matches[1] ?? null;
        
        if ($videoId) {
            return "https://www.youtube.com/embed/{$videoId}?autoplay=1&loop=1&playlist={$videoId}&controls=0&modestbranding=1&rel=0";
        }

        return null;
    }

    /**
     * Check if video is YouTube type
     */
    public function isYoutube()
    {
        return $this->video_type === 'youtube';
    }

    /**
     * Check if video is file upload type
     */
    public function isFile()
    {
        return $this->video_type === 'file';
    }

    /**
     * Get organization relationship
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
