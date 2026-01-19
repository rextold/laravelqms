<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'is_default',
        'auto_play',
        'loop',
        'shuffle',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'days_of_week',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'auto_play' => 'boolean',
        'loop' => 'boolean',
        'shuffle' => 'boolean',
        'is_active' => 'boolean',
        'days_of_week' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'priority' => 'integer',
    ];

    /**
     * Scope to get active playlists
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get playlists that should be playing at current time
     */
    public function scopeScheduledForNow($query)
    {
        $now = Carbon::now();
        $currentDate = $now->toDateString();
        $currentTime = $now->format('H:i:s');
        $currentDayOfWeek = $now->dayOfWeek === 0 ? 7 : $now->dayOfWeek; // Convert Sunday from 0 to 7

        return $query->where('is_active', true)
            ->where(function ($q) use ($currentDate) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $currentDate);
            })
            ->where(function ($q) use ($currentDate) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $currentDate);
            })
            ->where(function ($q) use ($currentTime) {
                $q->whereNull('start_time')
                  ->orWhere('start_time', '<=', $currentTime);
            })
            ->where(function ($q) use ($currentTime) {
                $q->whereNull('end_time')
                  ->orWhere('end_time', '>=', $currentTime);
            })
            ->where(function ($q) use ($currentDayOfWeek) {
                $q->whereNull('days_of_week')
                  ->orWhereJsonContains('days_of_week', $currentDayOfWeek);
            })
            ->orderBy('priority', 'desc');
    }

    /**
     * Get the default playlist for an organization
     */
    public static function getDefaultForOrganization($organizationId)
    {
        return self::where('organization_id', $organizationId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get the current active playlist for an organization
     */
    public static function getCurrentForOrganization($organizationId)
    {
        // First try to get a scheduled playlist
        $scheduledPlaylist = self::where('organization_id', $organizationId)
            ->scheduledForNow()
            ->first();

        if ($scheduledPlaylist) {
            return $scheduledPlaylist;
        }

        // Fall back to default playlist
        return self::getDefaultForOrganization($organizationId);
    }

    /**
     * Check if playlist is currently scheduled to play
     */
    public function isScheduledForNow()
    {
        $now = Carbon::now();
        $currentDate = $now->toDateString();
        $currentTime = $now->format('H:i:s');
        $currentDayOfWeek = $now->dayOfWeek === 0 ? 7 : $now->dayOfWeek;

        // Check date range
        if ($this->start_date && $this->start_date > $currentDate) {
            return false;
        }
        if ($this->end_date && $this->end_date < $currentDate) {
            return false;
        }

        // Check time range
        if ($this->start_time && $this->start_time > $currentTime) {
            return false;
        }
        if ($this->end_time && $this->end_time < $currentTime) {
            return false;
        }

        // Check days of week
        if ($this->days_of_week && !in_array($currentDayOfWeek, $this->days_of_week)) {
            return false;
        }

        return true;
    }

    /**
     * Get organization relationship
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get videos relationship
     */
    public function videos()
    {
        return $this->hasMany(Video::class)->orderBy('order');
    }

    /**
     * Get active videos relationship
     */
    public function activeVideos()
    {
        return $this->hasMany(Video::class)->where('is_active', true)->orderBy('order');
    }
}
