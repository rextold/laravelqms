<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueResetLog extends Model
{
    protected $table = 'queue_reset_logs';

    protected $fillable = [
        'organization_id',
        'user_id',
        'previous_sequence',
        'reset_to',
        'note',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
