<?php

namespace App\Models;

use App\Models\Traits\SchoolAware;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'start_time',
        'end_time',
        'location',
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
        'cot_document_path',
        'cot_document_generated_at',
        'form_template_id',
        'cot_indicator_version_id',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'confirmation_status',
        'rejection_reason',
        'rejection_notes',
        'confirmed_at',
        'rejected_at',
        'school_head_id',
        'finalized_at',
        'finalized_by',
    ];

    protected $casts = [
        'observation_date' => 'date',
        'overall_score' => 'decimal:2',
        'evidence_files' => 'array',
        'cancelled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function startTimeLabel(): Attribute
    {
        return Attribute::get(fn () => $this->start_time ? Carbon::parse($this->start_time)->format('h:i A') : null);
    }

    public function endTimeLabel(): Attribute
    {
        return Attribute::get(fn () => $this->end_time ? Carbon::parse($this->end_time)->format('h:i A') : null);
    }

    public function hasTimeSchedule(): Attribute
    {
        return Attribute::get(fn () => $this->start_time !== null || $this->end_time !== null);
    }

    /**
     * The form template used for this observation
     */
    public function formTemplate(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    /**
     * The COT indicator version pinned when this observation was created.
     * Kept as a historical reference even if the version is later archived.
     */
    public function cotIndicatorVersion(): BelongsTo
    {
        return $this->belongsTo(CotIndicatorVersion::class, 'cot_indicator_version_id');
    }

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
     * Observation has many AI Feedback entries (at observation level)
     */
    public function aiFeedbacks(): HasMany
    {
        return $this->hasMany(AiFeedback::class);
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
     * Observation has one EPOC Evaluation record
     */
    public function epocEvaluation()
    {
        return $this->hasOne(EpocEvaluation::class);
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
     * Observation is assigned to a School Head
     */
    public function schoolHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'school_head_id');
    }

    /**
     * User who finalized the observation
     */
    public function finalizedBy()
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    /**
     * User who cancelled the observation
     */
    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Check if the observation can be cancelled.
     * Allowed stages: pre_observation_planning, pre_conference, observation (with warning), post_conference (for record only)
     */
    public function canCancel(): bool
    {
        if ($this->status === 'cancelled' || $this->status === 'completed') {
            return false;
        }

        return in_array($this->stage, ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference']);
    }

    /**
     * Cancel the observation with a reason.
     */
    public function cancel(string $reason, ?string $internalNote = null): void
    {
        $this->logChange([
            'from_status' => $this->status,
            'to_status' => 'cancelled',
            'notes' => $internalNote ?? $reason,
        ]);

        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Check if the observation has been finalized by the supervisor
     */
    public function isFinalized(): bool
    {
        return $this->finalized_at !== null;
    }

    /**
     * Check if the observation can be finalized.
     * Only when status is cot_completed and not yet finalized.
     */
    public function canFinalize(): bool
    {
        return $this->status === 'cot_completed' && !$this->isFinalized();
    }

    /**
     * Finalize the observation
     */
    public function finalize(): void
    {
        $this->logChange([
            'from_status' => $this->status,
            'to_status' => 'completed',
            'notes' => 'Observation finalized by supervisor',
        ]);

        $this->update([
            'status' => 'completed',
            'finalized_at' => now(),
            'finalized_by' => Auth::id(),
        ]);
    }

    /**
     * Check if the observation can be confirmed by the teacher.
     * Only scheduled observations in pre_observation_planning stage can be confirmed.
     */
    public function canConfirm(): bool
    {
        if ($this->status === 'cancelled' || $this->status === 'completed') {
            return false;
        }

        return $this->confirmation_status === 'pending'
            && $this->stage === 'pre_observation_planning';
    }

    /**
     * Confirm the observation schedule.
     */
    public function confirm(): void
    {
        $this->update([
            'confirmation_status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Reject the observation schedule with a reason.
     */
    public function reject(string $reason, ?string $notes = null): void
    {
        $this->update([
            'confirmation_status' => 'rejected',
            'rejection_reason' => $reason,
            'rejection_notes' => $notes,
            'rejected_at' => now(),
        ]);
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

    /**
     * Scope for pending confirmation
     */
    public function scopePendingConfirmation($query)
    {
        return $query->where('confirmation_status', 'pending')
            ->where('stage', 'pre_observation_planning')
            ->where('status', '!=', 'cancelled');
    }

    /**
     * Scope for confirmed observations
     */
    public function scopeConfirmed($query)
    {
        return $query->where('confirmation_status', 'confirmed');
    }

    /**
     * Scope for rejected observations
     */
    public function scopeRejected($query)
    {
        return $query->where('confirmation_status', 'rejected');
    }
}
