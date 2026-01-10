<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class OrganizationSetting extends Model
{
    protected $table = 'organization_settings';

    protected static array $columnListingCache = [];

    protected $fillable = [
        'organization_id',
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

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    protected function resolveExistingColumn(array $candidates): ?string
    {
        $table = $this->getTable();

        if (!array_key_exists($table, self::$columnListingCache)) {
            try {
                self::$columnListingCache[$table] = Schema::getColumnListing($table);
            } catch (\Throwable $e) {
                self::$columnListingCache[$table] = [];
            }
        }

        $columns = self::$columnListingCache[$table];
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }

    public function getOrganizationLogoAttribute(): ?string
    {
        $column = $this->resolveExistingColumn(['organization_logo', 'company_logo', 'logo_path']);
        if (!$column) {
            return null;
        }
        return $this->attributes[$column] ?? null;
    }

    public function setOrganizationLogoAttribute($value): void
    {
        $column = $this->resolveExistingColumn(['organization_logo', 'company_logo', 'logo_path']);
        if (!$column) {
            return;
        }
        $this->attributes[$column] = $value;
    }

    public function getCompanyPhoneAttribute(): ?string
    {
        $column = $this->resolveExistingColumn(['company_phone', 'organization_phone', 'phone']);
        if (!$column) {
            return null;
        }
        return $this->attributes[$column] ?? null;
    }

    public function setCompanyPhoneAttribute($value): void
    {
        $column = $this->resolveExistingColumn(['company_phone', 'organization_phone', 'phone']);
        if (!$column) {
            return;
        }
        $this->attributes[$column] = $value;
    }

    public function getCompanyEmailAttribute(): ?string
    {
        $column = $this->resolveExistingColumn(['company_email', 'organization_email', 'email']);
        if (!$column) {
            return null;
        }
        return $this->attributes[$column] ?? null;
    }

    public function setCompanyEmailAttribute($value): void
    {
        $column = $this->resolveExistingColumn(['company_email', 'organization_email', 'email']);
        if (!$column) {
            return;
        }
        $this->attributes[$column] = $value;
    }

    public function getCompanyAddressAttribute(): ?string
    {
        $column = $this->resolveExistingColumn(['company_address', 'organization_address', 'address']);
        if (!$column) {
            return null;
        }
        return $this->attributes[$column] ?? null;
    }

    public function setCompanyAddressAttribute($value): void
    {
        $column = $this->resolveExistingColumn(['company_address', 'organization_address', 'address']);
        if (!$column) {
            return;
        }
        $this->attributes[$column] = $value;
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
        $column = $this->resolveExistingColumn(['organization_logo', 'company_logo', 'logo_path']);
        if ($column && !empty($this->attributes[$column])) {
            return asset('storage/' . $this->attributes[$column]);
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
