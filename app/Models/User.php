<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'display_name',
        'counter_number',
        'short_description',
        'is_online',
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
            ->orderBy('created_at')
            ->get();
    }
}
