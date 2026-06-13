<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachingAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'observation_id',
        'teacher_id',
        'supervisor_id',
        'focus_areas',
        'action_steps',
        'resources_needed',
        'success_indicators',
        'timeline',
        'supervisor_notes',
        'teacher_notes',
        'teacher_signed_at',
        'supervisor_signed_at',
        'teacher_signature',
        'supervisor_signature',
        'status',
    ];

    protected $casts = [
        'focus_areas' => 'array',
        'action_steps' => 'array',
        'teacher_signed_at' => 'datetime',
        'supervisor_signed_at' => 'datetime',
    ];

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFullySigned(): bool
    {
        return $this->teacher_signed_at !== null && $this->supervisor_signed_at !== null;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'draft' => 'bg-gray-100 text-gray-700',
            'active' => 'bg-green-100 text-green-700',
            'completed' => 'bg-blue-100 text-blue-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }
}
