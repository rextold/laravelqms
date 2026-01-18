<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected static function boot()
    {
        parent::boot();

        // When an admin's organization is updated, automatically update all their counters
        static::updating(function ($user) {
            // Check if this is an admin and organization_id has changed
            if ($user->isAdmin() && $user->isDirty('organization_id')) {
                $oldOrganizationId = $user->getOriginal('organization_id');
                $newOrganizationId = $user->getAttribute('organization_id');

                // Update all counters belonging to this admin's old organization to the new organization
                User::where('role', 'counter')
                    ->where('organization_id', $oldOrganizationId)
                    ->update(['organization_id' => $newOrganizationId]);
            }
        });
    }

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'display_name',
        'counter_number',
        'counter_code',
        'priority_code',
        'short_description',
        'is_online',
        'is_active',
        'organization_id',
        'session_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];    protected $casts = [
        'is_online' => 'boolean',
        'is_active' => 'boolean',
        'password' => 'hashed',
        'organization_id' => 'integer',
    ];

    public function queues()
    {
        return $this->hasMany(Queue::class, 'counter_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function company()
    {
        return $this->organization();
    }

    public function transferredQueues()
    {
        return $this->hasMany(Queue::class, 'transferred_to');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCounter(): bool
    {
        return $this->role === 'counter';
    }

    public function scopeOnlineCounters($query)
    {
        return $query->where('role', 'counter')
            ->where('is_online', true)
            ->orderBy('counter_number');
    }

    public function scopeCounters($query)
    {
        return $query->where('role', 'counter')
            ->orderBy('counter_number');
    }

    public function getCurrentQueue()
    {
        return $this->queues()
            ->whereIn('status', ['called', 'serving'])
            ->orderBy('called_at', 'desc')
            ->first();
    }

    public function getWaitingQueues()
    {
        return $this->queues()
            ->where('status', 'waiting')
            ->orderBy('updated_at')
            ->get();
    }

    public function getSkippedQueues()
    {
        return $this->queues()
            ->where('status', 'skipped')
            ->orderBy('updated_at')
            ->get();
    }

    public function getOnlineCounters()
    {
        return User::where('organization_id', $this->organization_id)
            ->where('role', 'counter')
            ->where('is_online', true)
            ->where('id', '!=', $this->id)
            ->orderBy('counter_number')
            ->get();
    }
}
