<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreConference extends Model
{
    use HasFactory;

    protected $fillable = [
        'observation_id',
        'discussion_notes',
        'finalized_focus',
        'conference_date',
        'teacher_reflection',
        'lesson_plan_review',
        'instructional_materials',
        'form_responses',
    ];

    protected $casts = [
        'conference_date' => 'datetime',
        'form_responses' => 'array',
    ];

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }
}
