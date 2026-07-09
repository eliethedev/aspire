<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreObservationPlanning extends Model
{
    use HasFactory;

    protected $fillable = [
        'observation_id',
        'lesson_plan_file',
        'ai_insights',
        'ai_insights_reviewed',
        'suggested_focus',
        'supervisor_notes',
        'observation_tool',
        'form_responses',
    ];

    protected $casts = [
        'ai_insights' => 'array',
        'ai_insights_reviewed' => 'boolean',
        'suggested_focus' => 'array',
        'form_responses' => 'array',
    ];

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }
}
