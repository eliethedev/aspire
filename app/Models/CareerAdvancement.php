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
 * This is a support-and-announce record: it STORES the supervisor's decision
 * and updates the teacher's `career_stage`, then notifies the teacher and
 * school head. It never edits the teacher's free-text position or salary.
 */
class CareerAdvancement extends Model
{
    use HasFactory;

    public const TYPE_ALLOW = 'allow';

    public const TYPE_ANNOUNCE = 'announce';

    public const STATUS_RECORDED = 'recorded';

    public const STATUS_ACKNOWLEDGED = 'acknowledged';

    protected $fillable = [
        'teacher_id',
        'supervisor_id',
        'from_career_stage',
        'to_career_stage',
        'type',
        'status',
        'remarks',
        'acted_at',
        'acknowledged_at',
    ];

    protected $casts = [
        'acted_at' => 'date',
        'acknowledged_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
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

    private function stageLabel(?string $key): string
    {
        if (! $key) {
            return '—';
        }

        return app(CareerStageResolver::class)->stageLabel($key) ?: $key;
    }
}
