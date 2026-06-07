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
        'domain',
        'indicator',
        'rating',
        'comments',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
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
     * Calculate percentage score (rating out of 5)
     */
    public function percentage(): float
    {
        return ($this->rating / 5) * 100;
    }

    /**
     * Check if rating is excellent (>= 90% = 4.5+)
     */
    public function isExcellent(): bool
    {
        return $this->percentage() >= 90;
    }

    /**
     * Check if rating needs improvement (< 70% = 3.5)
     */
    public function needsImprovement(): bool
    {
        return $this->percentage() < 70;
    }

    /**
     * Scope for ratings by domain
     */
    public function scopeByDomain($query, string $domain)
    {
        return $query->where('domain', $domain);
    }

    /**
     * Scope for high ratings (>= 4)
     */
    public function scopeHighRatings($query)
    {
        return $query->where('rating', '>=', 4);
    }
}
