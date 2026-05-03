<?php

namespace App\Models;

use App\Models\Traits\SchoolAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Observation extends Model
{
    use HasFactory, SchoolAware;

    protected $fillable = [
        'teacher_id',
        'supervisor_id',
        'school_id',
        'observation_date',
        'subject',
        'grade_section',
        'duration_minutes',
        'notes',
        'status',
    ];

    protected $casts = [
        'observation_date' => 'date',
        'duration_minutes' => 'integer',
    ];

    /**
     * Observation belongs to a Teacher
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Observation is conducted by a Supervisor (User)
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Observation has many COT Ratings
     * FLOW: Observation → COT Rating → AI Feedback → Prediction → Decision
     */
    public function cotRatings(): HasMany
    {
        return $this->hasMany(CotRating::class);
    }

    /**
     * Check if observation is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if observation has ratings
     */
    public function hasRatings(): bool
    {
        return $this->cotRatings()->exists();
    }

    /**
     * Scope for pending observations
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed observations
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
