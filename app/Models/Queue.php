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
        'notified_at',
        'skipped_at',
        'completed_at',
        'organization_id',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'notified_at' => 'datetime',
        'skipped_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function counter()
    {
        return $this->belongsTo(User::class, 'counter_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
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

    public function scopeSkipped($query)
    {
        return $query->where('status', 'skipped');
    }

    public function isNotifiedRecently()
    {
        if (!$this->notified_at) {
            return false;
        }
        return $this->notified_at->diffInSeconds(now()) < 5;
    }
}
