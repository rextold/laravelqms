<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarqueeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'speed',
        'is_active',
        'organization_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public static function getActive()
    {
        return self::where('is_active', true)->first();
    }

    public static function getActiveForOrganization($organizationId)
    {
        return self::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->first();
    }
}
