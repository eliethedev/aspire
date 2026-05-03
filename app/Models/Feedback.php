<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'cot_rating_id',
        'supervisor_id',
        'content',
        'type',
    ];

    /**
     * STRICT SEPARATION:
     * This model is for HUMAN/SUPERVISOR feedback only.
     * For AI-generated feedback, use AiFeedback model.
     */

    /**
     * Feedback belongs to a COT Rating
     */
    public function cotRating(): BelongsTo
    {
        return $this->belongsTo(CotRating::class);
    }

    /**
     * Feedback is given by a Supervisor (User)
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Check if feedback is a strength
     */
    public function isStrength(): bool
    {
        return $this->type === 'strength';
    }

    /**
     * Check if feedback is for improvement
     */
    public function isImprovement(): bool
    {
        return $this->type === 'improvement';
    }

    /**
     * Scope for feedback by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for strengths
     */
    public function scopeStrengths($query)
    {
        return $query->where('type', 'strength');
    }

    /**
     * Scope for improvements
     */
    public function scopeImprovements($query)
    {
        return $query->where('type', 'improvement');
    }
}
