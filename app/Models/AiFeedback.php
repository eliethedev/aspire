<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiFeedback extends Model
{
    use HasFactory;

    protected $table = 'ai_feedback';

    protected $fillable = [
        'cot_rating_id',
        'analysis',
        'recommendations',
        'strengths',
        'areas_for_improvement',
        'confidence_score',
        'model_version',
    ];

    protected $casts = [
        'recommendations' => 'array',
        'strengths' => 'array',
        'areas_for_improvement' => 'array',
        'confidence_score' => 'decimal:2',
    ];

    /**
     * STRICT SEPARATION:
     * This model is for AI-GENERATED feedback only.
     * For human/supervisor feedback, use Feedback model.
     */

    /**
     * AI Feedback belongs to a COT Rating
     */
    public function cotRating(): BelongsTo
    {
        return $this->belongsTo(CotRating::class);
    }

    /**
     * Check if AI feedback has high confidence
     */
    public function hasHighConfidence(): bool
    {
        return $this->confidence_score >= 0.85;
    }

    /**
     * Check if AI feedback has medium confidence
     */
    public function hasMediumConfidence(): bool
    {
        return $this->confidence_score >= 0.60 && $this->confidence_score < 0.85;
    }

    /**
     * Check if AI feedback needs review (low confidence)
     */
    public function needsReview(): bool
    {
        return $this->confidence_score < 0.60;
    }

    /**
     * Get formatted analysis
     */
    public function formattedAnalysis(): string
    {
        return nl2br(e($this->analysis));
    }

    /**
     * Scope for high confidence AI feedback
     */
    public function scopeHighConfidence($query)
    {
        return $query->where('confidence_score', '>=', 0.85);
    }

    /**
     * Scope for feedback by model version
     */
    public function scopeByModelVersion($query, string $version)
    {
        return $query->where('model_version', $version);
    }
}
