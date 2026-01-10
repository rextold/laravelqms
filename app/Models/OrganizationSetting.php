<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationSetting extends Model
{
    protected $table = 'organization_settings';

    protected $fillable = [
        'organization_id',
        'code',
        'primary_color',
        'secondary_color',
        'accent_color',
        'text_color',
        'address',
        'phone',
        'email',
        'queue_number_digits',
        'is_active',
        'logo_path',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Get the organization settings (singleton pattern)
     */
    public static function getSettings()
    {
        // Get organization from session or use first active organization
        $organization = session('organization');
        
        if (!$organization) {
            $organization = Organization::where('is_active', true)->first();
        }
        
        if (!$organization) {
            // No organization found, return null or throw exception
            return null;
        }
        
        // Get settings for this organization
        $settings = self::where('organization_id', $organization->id)->first();
        
        if (!$settings) {
            // Create default settings for this organization
            $settings = self::create([
                'organization_id' => $organization->id,
                'code' => $organization->organization_code,
                'primary_color' => '#3b82f6',
                'secondary_color' => '#8b5cf6',
                'accent_color' => '#10b981',
                'text_color' => '#ffffff',
                'queue_number_digits' => 4,
                'is_active' => true,
            ]);
        }
        
        return $settings;
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

    /**
     * Get gradient CSS
     */
    public function getGradientCssAttribute()
    {
        return "background: linear-gradient(135deg, {$this->primary_color}, {$this->secondary_color});";
    }
}
