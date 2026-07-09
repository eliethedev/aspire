<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormTemplate extends Model
{
    use HasFactory;

    public const OBSERVATION_TYPES = [
        null => 'All Types',
        'teacher_observation' => 'Teacher',
        'school_head_observation' => 'School Head',
    ];

    protected $fillable = [
        'name',
        'description',
        'school_year',
        'observation_type',
        'is_active',
        'version',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'integer',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(FormSection::class, 'template_id')->orderBy('order');
    }

    public function scopeActive($query, ?string $schoolYear = null, ?string $observationType = null)
    {
        $query->where('is_active', true);

        if ($schoolYear) {
            $query->where('school_year', $schoolYear);
        }

        if ($observationType) {
            $query->where(function ($q) use ($observationType) {
                $q->where('observation_type', $observationType)
                  ->orWhereNull('observation_type');
            });
        }

        return $query->orderByRaw('IFNULL(observation_type, ?) = ? DESC', [$observationType, $observationType]);
    }

    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class, 'form_template_id');
    }

    public function activate(): void
    {
        static::where('is_active', true)
            ->where('school_year', $this->school_year)
            ->where('observation_type', $this->observation_type)
            ->update(['is_active' => false]);

        $this->update(['is_active' => true, 'version' => $this->version + 1]);
    }

    public function observationTypeLabel(): string
    {
        return match ($this->observation_type) {
            'teacher_observation' => 'Teacher',
            'school_head_observation' => 'School Head',
            default => 'All Types',
        };
    }
}
