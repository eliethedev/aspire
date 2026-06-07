<?php

namespace App\Models;

use App\Models\Traits\SchoolAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Observation extends Model
{
    use HasFactory, SchoolAware;

    protected $fillable = [
        'observer_id',
        'observer_type',
        'observee_id',
        'observee_type',
        'observation_type',
        'observation_date',
        'stage',
        'overall_score',
        'notes',
        'status',
        'school_year',
        'quarter',
        'observation_number',
        'subject',
        'grade_level',
        'observation_mode',
        'evidence_files',
    ];

    protected $casts = [
        'observation_date' => 'date',
        'overall_score' => 'decimal:2',
        'evidence_files' => 'array',
    ];

    /**
     * Observation belongs to an Observer (polymorphic - can be Supervisor or School Head)
     */
    public function observer()
    {
        return $this->morphTo();
    }

    /**
     * Observation belongs to an Observee (polymorphic - can be Teacher or School Head)
     */
    public function observee()
    {
        return $this->morphTo();
    }

    /**
     * Observation belongs to a Teacher (legacy relationship for backward compatibility)
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Observation is conducted by a Supervisor (User) (legacy relationship for backward compatibility)
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
     * Observation has many logs
     */
    public function logs()
    {
        return $this->hasMany(ObservationLog::class);
    }

    /**
     * Log a stage or status change
     */
    public function logChange(array $data): ObservationLog
    {
        return $this->logs()->create([
            'user_id' => Auth::id(),
            'from_stage' => $data['from_stage'] ?? $this->stage,
            'to_stage' => $data['to_stage'] ?? $this->stage,
            'from_status' => $data['from_status'] ?? $this->status,
            'to_status' => $data['to_status'] ?? $this->status,
            'notes' => $data['notes'] ?? null,
        ]);
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
     * Check if this is a teacher observation
     */
    public function isTeacherObservation(): bool
    {
        return $this->observation_type === 'teacher_observation';
    }

    /**
     * Check if this is a school head observation
     */
    public function isSchoolHeadObservation(): bool
    {
        return $this->observation_type === 'school_head_observation';
    }

    /**
     * Scope for teacher observations
     */
    public function scopeTeacherObservations($query)
    {
        return $query->where('observation_type', 'teacher_observation');
    }

    /**
     * Scope for school head observations
     */
    public function scopeSchoolHeadObservations($query)
    {
        return $query->where('observation_type', 'school_head_observation');
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
