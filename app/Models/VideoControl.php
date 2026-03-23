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
        'is_playing'       => 'boolean',
        'is_shuffle'       => 'boolean',
        'is_sequence'      => 'boolean',
        'volume'           => 'integer',
        'bell_volume'      => 'integer',
        'current_video_id' => 'integer',
        'organization_id'  => 'integer',
    ];

    public function currentVideo()
    {
        return $this->belongsTo(Video::class, 'current_video_id');
    }

    public static function getCurrent(?int $organizationId = null): self
    {
        $query = self::query();
        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        try {
            $record = $query->first();
        } catch (\Illuminate\Database\QueryException $e) {
            // organization_id column may not exist yet (migration pending).
            // Fall back to unscoped query so the monitor keeps working.
            $record = self::query()->first();
        }

        if ($record) {
            return $record;
        }

        $attributes = ['is_playing' => true, 'volume' => 50, 'bell_volume' => 100];
        if ($organizationId) {
            $attributes['organization_id'] = $organizationId;
        }

        try {
            return self::create($attributes);
        } catch (\Illuminate\Database\QueryException $e) {
            // organization_id column still missing — create without it.
            return self::create(['is_playing' => true, 'volume' => 50, 'bell_volume' => 100]);
        }
    }
}
