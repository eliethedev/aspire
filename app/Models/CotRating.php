<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CotRating extends Model
{
    use HasFactory;

    protected $table = 'cot_ratings';

    protected $fillable = [
        'observation_id',
        'rating_category',
        'score',
        'max_score',
        'comments',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    /**
     * COT Rating belongs to an Observation
     * FLOW: Observation → COT Rating → AI Feedback → Prediction → Decision
     */
    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }

    /**
     * COT Rating has one Human/Supervisor Feedback
     * STRICT SEPARATION: feedback = human/supervisor feedback
     */
    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }

    /**
     * COT Rating has one AI Feedback
     * STRICT SEPARATION: ai_feedback = AI-generated feedback
     */
    public function aiFeedback(): HasOne
    {
        return $this->hasOne(AiFeedback::class, 'cot_rating_id');
    }

    /**
     * Calculate percentage score
     */
    public function percentage(): float
    {
        if ($this->max_score == 0) {
            return 0;
        }
        return ($this->score / $this->max_score) * 100;
    }

    /**
     * Check if rating is excellent (>= 90%)
     */
    public function isExcellent(): bool
    {
        return $this->percentage() >= 90;
    }

    /**
     * Check if rating needs improvement (< 70%)
     */
    public function needsImprovement(): bool
    {
        return $this->percentage() < 70;
    }

    /**
     * Scope for ratings by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('rating_category', $category);
    }

    /**
     * Scope for high scores (>= 80%)
     */
    public function scopeHighScores($query)
    {
        return $query->whereRaw('(score / max_score) >= 0.8');
    }
}
