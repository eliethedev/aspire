<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
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
        'start_time',
        'end_time',
        'location',
        'mode',
        'star_notes',
        'areas_for_improvement',
        'challenges_facing_teacher',
        'ideas_for_addressing_challenges',
        'prioritized_next_steps',
        'teacher_reflection',
        'supervisor_notes',
        'form_responses',
    ];

    protected $casts = [
        'ai_comparison' => 'array',
        'conference_date' => 'datetime',
        'form_responses' => 'array',
    ];

    public function startTimeLabel(): Attribute
    {
        return Attribute::get(fn () => $this->start_time ? Carbon::parse($this->start_time)->format('h:i A') : null);
    }

    public function endTimeLabel(): Attribute
    {
        return Attribute::get(fn () => $this->end_time ? Carbon::parse($this->end_time)->format('h:i A') : null);
    }

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }
}
