<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';

    protected $fillable = [
        'organization_code',
        'organization_name',
        'organization_logo',
        'primary_color',
        'secondary_color',
        'accent_color',
        'text_color',
        'organization_address',
        'organization_phone',
        'organization_email',
        'queue_number_digits',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'organization_id');
    }

    public function queues()
    {
        return $this->hasMany(Queue::class, 'organization_id');
    }

    public function videoControls()
    {
        return $this->hasMany(VideoControl::class, 'organization_id');
    }

    public function videos()
    {
        return $this->hasMany(Video::class, 'organization_id');
    }

    public function marqueeSettings()
    {
        return $this->hasMany(MarqueeSetting::class, 'organization_id');
    }

    public function setting()
    {
        return $this->hasOne(OrganizationSetting::class, 'organization_id');
    }

    /**
     * Find organization by code
     */
    public static function findByCode($code)
    {
        return self::where('organization_code', $code)->where('is_active', true)->first();
    }

    /**
     * Get logo URL
     */
    public function getLogoUrlAttribute()
    {
        if ($this->organization_logo) {
            return asset('storage/' . $this->organization_logo);
        }
        return null;
    }

    // Backward compatibility accessors for legacy code using company_* columns
    public function getCompanyCodeAttribute()
    {
        return $this->organization_code;
    }

    public function getCompanyNameAttribute()
    {
        return $this->organization_name;
    }

    public function getCompanyLogoAttribute()
    {
        return $this->organization_logo;
    }
}
