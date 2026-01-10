<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoControl extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_playing',
        'volume',
        'bell_volume',
        'bell_sound_path',
        'current_video_id',
        'repeat_mode',
        'is_shuffle',
        'is_sequence',
        'organization_id',
    ];

    protected $casts = [
        'is_playing' => 'boolean',
        'is_shuffle' => 'boolean',
        'is_sequence' => 'boolean',
    ];

    public function currentVideo()
    {
        return $this->belongsTo(Video::class, 'current_video_id');
    }

    public static function getCurrent()
    {
        return self::first() ?? self::create([
            'is_playing' => true,
            'volume' => 50,
            'bell_volume' => 100,
        ]);
    }
}
