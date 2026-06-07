<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservationLog extends Model
{
    protected $fillable = [
        'observation_id',
        'user_id',
        'from_stage',
        'to_stage',
        'from_status',
        'to_status',
        'notes',
    ];

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
