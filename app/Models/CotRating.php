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
        'indicator_code',
        'domain',
        'indicator',
        'rating',
        'not_observed',
        'comments',
    ];

    protected $casts = [
        'rating' => 'integer',
        'not_observed' => 'boolean',
    ];

    /**
     * COT Rating belongs to an Observation
     * FLOW: Observation ? COT Rating ? AI Feedback ? Prediction ? Decision
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
     * Get the numeric rating value, or 0 if Not Observed
     */
    public function numericRating(): int
    {
        return $this->not_observed ? 0 : ($this->rating ?? 0);
    }

    /**
     * Calculate percentage score based on 2-6 scale (max = 6).
     */
    public function percentage(): float
    {
        if ($this->isNotObserved()) {
            return 0;
        }
        return round(($this->rating / 6) * 100, 1);
    }

    /**
     * Check if the indicator was Not Observed
     */
    public function isNotObserved(): bool
    {
        return $this->not_observed;
    }

    /**
     * Get the descriptive label for the rating
     */
    public function descriptiveLabel(): string
    {
        if ($this->not_observed) {
            return 'Not Observed';
        }

        return match ($this->rating) {
            6 => 'Outstanding',
            5 => 'Very Satisfactory',
            4 => 'Satisfactory',
            3 => 'Unsatisfactory',
            2 => 'Poor',
            default => 'Unknown',
        };
    }

    /**
     * Get CSS class for the rating badge
     */
    public function ratingBadgeClass(): string
    {
        if ($this->not_observed) {
            return 'bg-gray-200 text-gray-600';
        }

        return match ($this->rating) {
            6 => 'bg-green-100 text-green-800',
            5 => 'bg-blue-100 text-blue-800',
            4 => 'bg-yellow-100 text-yellow-800',
            3 => 'bg-orange-100 text-orange-800',
            2 => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Scope for ratings by domain
     */
    public function scopeByDomain($query, string $domain)
    {
        return $query->where('domain', $domain);
    }

    /**
     * Scope for high ratings (>= 5)
     */
    public function scopeHighRatings($query)
    {
        return $query->where('rating', '>=', 5)->where('not_observed', false);
    }
}
