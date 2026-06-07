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
        'star_notes',
        'areas_for_improvement',
        'challenges_facing_teacher',
        'ideas_for_addressing_challenges',
        'prioritized_next_steps',
        'teacher_reflection',
        'supervisor_notes',
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
