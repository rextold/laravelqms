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

        // When an admin's company is updated, automatically update all their counters
        static::updating(function ($user) {
            // Check if this is an admin and company_id has changed
            if ($user->isAdmin() && $user->isDirty('company_id')) {
                $oldCompanyId = $user->getOriginal('company_id');
                $newCompanyId = $user->getAttribute('company_id');

                // Update all counters belonging to this admin's old company to the new company
                User::where('role', 'counter')
                    ->where('company_id', $oldCompanyId)
                    ->update(['company_id' => $newCompanyId]);
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
        'priority_code',
        'short_description',
        'is_online',
        'company_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'password' => 'hashed',
    ];

    public function queues()
    {
        return $this->hasMany(Queue::class, 'counter_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
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
}
