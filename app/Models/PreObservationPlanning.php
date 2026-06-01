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
        'suggested_focus',
    ];

    protected $casts = [
        'ai_insights' => 'array',
        'suggested_focus' => 'array',
    ];

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }
}
