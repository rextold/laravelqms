<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_id',
        'code',
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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the company settings (singleton pattern)
     */
    public static function getSettings()
    {
        // Get company from session or use first active company
        $company = session('company');
        
        if (!$company) {
            $company = Company::where('is_active', true)->first();
        }
        
        if (!$company) {
            // No company found, return null or throw exception
            return null;
        }
        
        // Get settings for this company
        $settings = self::where('company_id', $company->id)->first();
        
        if (!$settings) {
            // Create default settings for this company
            $settings = self::create([
                'company_id' => $company->id,
                'code' => $company->company_code,
                'company_name' => $company->company_name,
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
        if ($this->company_logo) {
            return asset('storage/' . $this->company_logo);
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
