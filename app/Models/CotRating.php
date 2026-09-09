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
        'not_applicable',
        'comments',
    ];

    protected $casts = [
        'rating' => 'integer',
        'not_observed' => 'boolean',
        'not_applicable' => 'boolean',
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
     * Get the numeric rating value, or 0 if Not Observed / Not Applicable
     */
    public function numericRating(): int
    {
        return ($this->not_observed || $this->not_applicable) ? 0 : ($this->rating ?? 0);
    }

    /**
     * Calculate percentage score. The scale max defaults to 6 (Teacher I-III)
     * but may be overridden for higher-stage instruments (7 or 8).
     */
    public function percentage(?int $max = null): float
    {
        if ($this->isNotObserved() || $this->isNotApplicable()) {
            return 0;
        }
        $max = $max ?? 6;
        return round(($this->rating / $max) * 100, 1);
    }

    /**
     * Check if the indicator was Not Observed
     */
    public function isNotObserved(): bool
    {
        return (bool) $this->not_observed;
    }

    /**
     * Check if the indicator was marked Not Applicable (excluded from scoring)
     */
    public function isNotApplicable(): bool
    {
        return (bool) $this->not_applicable;
    }

    /**
     * Get the descriptive label for the overall COT score.
     * DepEd bands: Outstanding, Very Satisfactory, Satisfactory, Poor, Needs Improvement.
     * The scale max defaults to 6 but may be overridden for higher stages.
     */
    public static function descriptiveTotal(?float $score, ?float $max = 6.0): string
    {
        $max = $max ?: 6.0;
        // Thresholds are scaled proportionally from the reference 6-point scale
        // (Outstanding >= 5.5, VS >= 4.5, S >= 3.5, Poor >= 2.5) so the exact
        // behaviour for max = 6 is preserved.
        $outstanding = $max * 5.5 / 6.0;
        $verySat = $max * 4.5 / 6.0;
        $sat = $max * 3.5 / 6.0;
        $poor = $max * 2.5 / 6.0;

        return match (true) {
            $score === null => 'No Score Yet',
            $score >= $outstanding => 'Outstanding',
            $score >= $verySat => 'Very Satisfactory',
            $score >= $sat => 'Satisfactory',
            $score >= $poor => 'Poor',
            default => 'Needs Improvement',
        };
    }

    /**
     * Get the descriptive label for the rating.
     * The scale map (value => label) lets higher-stage instruments (3-7, 4-8)
     * produce the correct descriptor; defaults to the global 2-6 scale.
     */
    public function descriptiveLabel(?array $scale = null): string
    {
        if ($this->not_applicable) {
            return 'Not Applicable';
        }

        if ($this->not_observed) {
            return 'Not Observed';
        }

        $scale = $scale ?? config('cot.rating_scale', []);

        return $scale[$this->rating] ?? 'Unknown';
    }

    /**
     * Get CSS class for the rating badge
     */
    public function ratingBadgeClass(): string
    {
        if ($this->not_applicable) {
            return 'bg-gray-100 text-gray-500';
        }

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
        return $query->where('rating', '>=', 5)
            ->where('not_observed', false)
            ->where('not_applicable', false);
    }

    /**
     * Scope excluding indicators marked Not Applicable
     */
    public function scopeScored($query)
    {
        return $query->where('not_observed', false)->where('not_applicable', false);
    }
}
