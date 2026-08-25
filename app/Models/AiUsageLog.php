<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    protected $fillable = [
        'stage',
        'provider',
        'model',
        'prompt_tokens',
        'response_tokens',
        'total_tokens',
        'response_time_ms',
        'success',
        'fallback_used',
        'error_message',
        'observation_id',
        'user_id',
    ];

    protected $casts = [
        'success' => 'boolean',
        'fallback_used' => 'boolean',
        'prompt_tokens' => 'integer',
        'response_tokens' => 'integer',
        'total_tokens' => 'integer',
        'response_time_ms' => 'integer',
    ];

    public function observation()
    {
        $this->belongsTo(Observation::class);
    }

    public function user()
    {
        $this->belongsTo(User::class);
    }
}
