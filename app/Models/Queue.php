<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_number',
        'counter_id',
        'status',
        'transferred_to',
        'called_at',
        'completed_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function counter()
    {
        return $this->belongsTo(User::class, 'counter_id');
    }

    public function transferredCounter()
    {
        return $this->belongsTo(User::class, 'transferred_to');
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeCalled($query)
    {
        return $query->where('status', 'called');
    }

    public function scopeServing($query)
    {
        return $query->where('status', 'serving');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
