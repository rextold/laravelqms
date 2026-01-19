<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisplaySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'display_mode',
        'video_fit',
        'auto_advance_time',
        'show_queue_info',
        'show_clock',
        'show_date',
        'show_weather',
        'background_color',
        'text_color',
        'accent_color',
        'font_size',
        'transition_effect',
        'transition_duration',
        'screen_saver_enabled',
        'screen_saver_timeout',
        'brightness',
        'contrast',
        'volume_control',
        'mute_during_hours',
        'display_resolution',
        'refresh_rate',
        'is_active',
    ];

    protected $casts = [
        'show_queue_info' => 'boolean',
        'show_clock' => 'boolean',
        'show_date' => 'boolean',
        'show_weather' => 'boolean',
        'screen_saver_enabled' => 'boolean',
        'volume_control' => 'boolean',
        'mute_during_hours' => 'array',
        'is_active' => 'boolean',
        'auto_advance_time' => 'integer',
        'transition_duration' => 'integer',
        'screen_saver_timeout' => 'integer',
        'brightness' => 'integer',
        'contrast' => 'integer',
        'font_size' => 'integer',
        'refresh_rate' => 'integer',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public static function getForOrganization($organizationId)
    {
        return self::where('organization_id', $organizationId)->first() ?? self::createDefault($organizationId);
    }

    public static function createDefault($organizationId)
    {
        return self::create([
            'organization_id' => $organizationId,
            'display_mode' => 'fullscreen',
            'video_fit' => 'cover',
            'auto_advance_time' => 30,
            'show_queue_info' => true,
            'show_clock' => true,
            'show_date' => true,
            'show_weather' => false,
            'background_color' => '#000000',
            'text_color' => '#ffffff',
            'accent_color' => '#3b82f6',
            'font_size' => 16,
            'transition_effect' => 'fade',
            'transition_duration' => 1000,
            'screen_saver_enabled' => true,
            'screen_saver_timeout' => 300,
            'brightness' => 100,
            'contrast' => 100,
            'volume_control' => true,
            'mute_during_hours' => [],
            'display_resolution' => '1920x1080',
            'refresh_rate' => 60,
            'is_active' => true,
        ]);
    }
}