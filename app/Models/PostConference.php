<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostConference extends Model
{
    use HasFactory;

    protected $fillable = [
        'observation_id',
        'ai_comparison',
        'feedback',
        'conference_date',
    ];

    protected $casts = [
        'ai_comparison' => 'array',
        'conference_date' => 'datetime',
    ];

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }
}
