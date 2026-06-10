<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiInsight extends Model
{
    protected $fillable = [
        'insight_type',
        'insight_data',
        'model_version',
        'confidence_score',
        'source',
    ];

    protected $casts = [
        'insight_data' => 'array',
        'confidence_score' => 'decimal:2',
    ];

    public function insightable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('insight_type', $type);
    }

    public function scopeHighConfidence($query)
    {
        return $query->where('confidence_score', '>=', 0.85);
    }

    public function hasHighConfidence(): bool
    {
        return $this->confidence_score >= 0.85;
    }

    public function needsReview(): bool
    {
        return $this->confidence_score < 0.60;
    }
}
