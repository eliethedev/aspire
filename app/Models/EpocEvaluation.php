<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EpocEvaluation extends Model
{
    use HasFactory;

    protected $table = 'epoc_evaluations';

    protected $fillable = [
        'observation_id',
        'epoc_template_id',
        'school_head_name',
        'observation_date',
        'narrative_observation',
        'agreement',
        'overall_score',
    ];

    protected $casts = [
        'observation_date' => 'date',
        'overall_score' => 'decimal:2',
    ];

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EpocTemplate::class, 'epoc_template_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(EpocRating::class, 'epoc_evaluation_id');
    }
}
