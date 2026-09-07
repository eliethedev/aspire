<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A supervisor's or admin's readiness assessment for a ratee.
 *
 * Assessments are append-only history: every save records a new row with a
 * snapshot of the ratee's position/career stage/framework at assessment time.
 * Saving an assessment NEVER changes the ratee's position or career stage -
 * it only supports (never automates) the official promotion process.
 */
class CareerProgressionAssessment extends Model
{
    use HasFactory;

    public const STATUS_NEEDS_DEVELOPMENT = 'needs_development';

    public const STATUS_FOR_REVIEW = 'for_review';

    public const STATUS_READY_FOR_CONSIDERATION = 'ready_for_consideration';

    public const STATUSES = [
        self::STATUS_NEEDS_DEVELOPMENT,
        self::STATUS_FOR_REVIEW,
        self::STATUS_READY_FOR_CONSIDERATION,
    ];

    protected $fillable = [
        'ratee_type',
        'ratee_id',
        'evaluator_id',
        'status',
        'target_career_stage',
        'remarks',
        'assessed_at',
        'position',
        'career_stage',
        'framework',
    ];

    protected $casts = [
        'assessed_at' => 'date',
    ];

    public function ratee(): MorphTo
    {
        return $this->morphTo();
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    /**
     * Resolved label for the snapshotted target career stage key
     * (e.g. "Teacher IV-VII · Career Stage II"). Falls back to the raw key.
     */
    public function targetStageLabel(): string
    {
        if (! $this->target_career_stage) {
            return '';
        }

        $stageLabel = config("career_stages.career_stage_labels.{$this->target_career_stage}");

        if ($stageLabel === null) {
            return ucwords(str_replace('_', ' ', $this->target_career_stage));
        }

        $enum = \App\Enums\TeacherCareerStage::tryFrom($this->target_career_stage);

        return $enum !== null ? "{$enum->label()} · {$stageLabel}" : $stageLabel;
    }

    /**
     * User-friendly label for the snapshotted position
     * (e.g. "teacher_i" → "Teacher I"). Falls back to the raw value.
     */
    public function positionLabel(): ?string
    {
        return \App\Models\Teacher::positionLabelFor($this->position) ?? $this->position;
    }

    public function statusLabel(): string
    {
        return self::statusLabelFor($this->status);
    }

    public function statusBadgeClass(): string
    {
        return self::statusBadgeFor($this->status);
    }

    /**
     * Status select options: value => label.
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEEDS_DEVELOPMENT => 'Needs Development',
            self::STATUS_FOR_REVIEW => 'For Review',
            self::STATUS_READY_FOR_CONSIDERATION => 'Ready for Consideration',
        ];
    }

    public static function statusLabelFor(string $status): string
    {
        return self::statusOptions()[$status] ?? ucwords(str_replace('_', ' ', $status));
    }

    public static function statusBadgeFor(string $status): string
    {
        return match ($status) {
            self::STATUS_NEEDS_DEVELOPMENT => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
            self::STATUS_FOR_REVIEW => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
            self::STATUS_READY_FOR_CONSIDERATION => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
            default => 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400',
        };
    }
}
