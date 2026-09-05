<?php

namespace App\Models;

use App\Services\CareerStageResolver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A supervisor's record that a teacher was allowed / announced to have
 * achieved a higher career stage.
 *
 * Workflow: a supervisor creates a record in PENDING_APPROVAL state (the
 * teacher's `career_stage` is NOT advanced yet). The school head either
 * approves (which advances the teacher's `career_stage`, records the approval
 * and congratulates the teacher) or rejects it (the teacher stays at their
 * current stage). It never edits the teacher's free-text position or salary.
 */
class CareerAdvancement extends Model
{
    use HasFactory;

    public const TYPE_ALLOW = 'allow';

    public const TYPE_ANNOUNCE = 'announce';

    public const STATUS_PENDING_APPROVAL = 'pending_approval';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_RECORDED = 'recorded';

    public const STATUS_ACKNOWLEDGED = 'acknowledged';

    protected $fillable = [
        'teacher_id',
        'supervisor_id',
        'school_head_id',
        'from_career_stage',
        'to_career_stage',
        'type',
        'status',
        'remarks',
        'school_head_remarks',
        'acted_at',
        'acknowledged_at',
        'school_head_approved_at',
        'school_head_rejected_at',
    ];

    protected $casts = [
        'acted_at' => 'date',
        'acknowledged_at' => 'datetime',
        'school_head_approved_at' => 'datetime',
        'school_head_rejected_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function schoolHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'school_head_id');
    }

    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function typeLabel(): string
    {
        return $this->type === self::TYPE_ALLOW ? 'Allowed' : 'Announced';
    }

    public function typeBadgeClass(): string
    {
        return $this->type === self::TYPE_ALLOW
            ? 'bg-sky-100 text-sky-700 border-sky-200'
            : 'bg-emerald-100 text-emerald-700 border-emerald-200';
    }

    public function fromStageLabel(): string
    {
        return $this->stageLabel($this->from_career_stage);
    }

    public function toStageLabel(): string
    {
        return $this->stageLabel($this->to_career_stage);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_APPROVAL => 'Pending School Head Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_APPROVAL => 'bg-amber-100 text-amber-700 border-amber-200',
            self::STATUS_APPROVED => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            self::STATUS_REJECTED => 'bg-red-100 text-red-700 border-red-200',
            self::STATUS_CANCELLED => 'bg-gray-100 text-gray-600 border-gray-200',
            default => 'bg-gray-100 text-gray-600 border-gray-200',
        };
    }

    private function stageLabel(?string $key): string
    {
        if (! $key) {
            return '—';
        }

        return app(CareerStageResolver::class)->stageLabel($key) ?: $key;
    }
}
