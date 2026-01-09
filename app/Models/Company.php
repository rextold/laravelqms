<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_code',
        'company_name',
        'company_logo',
        'primary_color',
        'secondary_color',
        'accent_color',
        'text_color',
        'company_address',
        'company_phone',
        'company_email',
        'queue_number_digits',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    public function videoControls()
    {
        return $this->hasMany(VideoControl::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function marqueeSettings()
    {
        return $this->hasMany(MarqueeSetting::class);
    }

    public function setting()
    {
        return $this->hasOne(CompanySetting::class);
    }

    /**
     * Find company by code
     */
    public static function findByCode($code)
    {
        return self::where('company_code', $code)->where('is_active', true)->first();
    }

    /**
     * Get logo URL
     */
    public function getLogoUrlAttribute()
    {
        if ($this->company_logo) {
            return asset('storage/' . $this->company_logo);
        }
        return null;
    }
}
