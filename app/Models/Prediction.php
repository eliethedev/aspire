<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'prediction_type',
        'predicted_outcome',
        'confidence_level',
        'factors_considered',
        'action_recommended',
        'status',
        'valid_until',
    ];

    protected $casts = [
        'confidence_level' => 'decimal:2',
        'factors_considered' => 'array',
        'valid_until' => 'date',
    ];

    /**
     * Prediction belongs to a Teacher
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Check if prediction is for performance
     */
    public function isPerformance(): bool
    {
        return $this->prediction_type === 'performance';
    }

    /**
     * Check if prediction is for promotion
     */
    public function isPromotion(): bool
    {
        return $this->prediction_type === 'promotion';
    }

    /**
     * Check if prediction is for training
     */
    public function isTraining(): bool
    {
        return $this->prediction_type === 'training';
    }

    /**
     * Check if prediction is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->valid_until->isFuture();
    }

    /**
     * Check if prediction has expired
     */
    public function hasExpired(): bool
    {
        return $this->valid_until->isPast();
    }

    /**
     * Get confidence level as text
     */
    public function confidenceText(): string
    {
        if ($this->confidence_level >= 0.85) {
            return 'High';
        } elseif ($this->confidence_level >= 0.60) {
            return 'Medium';
        }
        return 'Low';
    }

    /**
     * Scope for active predictions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('valid_until', '>', now());
    }

    /**
     * Scope by prediction type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('prediction_type', $type);
    }

    /**
     * Scope for expired predictions
     */
    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', now());
    }
}
