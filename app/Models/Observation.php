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
        'observation_date',
        'stage',
        'overall_score',
        'notes',
        'status',
    ];

    protected $casts = [
        'observation_date' => 'date',
        'overall_score' => 'decimal:2',
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
     * Observation has one Pre-Observation Planning record
     */
    public function preObservationPlanning()
    {
        return $this->hasOne(PreObservationPlanning::class);
    }

    /**
     * Observation has one Pre-Conference record
     */
    public function preConference()
    {
        return $this->hasOne(PreConference::class);
    }

    /**
     * Observation has one Post-Conference record
     */
    public function postConference()
    {
        return $this->hasOne(PostConference::class);
    }

    /**
     * Check if observation is completed
     */
    public function isCompleted(): bool
    {
        return $this->stage === 'post_conference';
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
        return $query->whereIn('stage', ['pre_observation_planning', 'pre_conference', 'observation']);
    }

    /**
     * Scope for completed observations
     */
    public function scopeCompleted($query)
    {
        return $query->where('stage', 'post_conference');
    }
}
