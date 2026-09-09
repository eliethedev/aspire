<?php

namespace App\Models;

use App\Enums\TeacherCareerStage;
use App\Models\Traits\SchoolAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory, SchoolAware;

    protected $fillable = [
        'user_id',
        'school_id',
        'department',
        'years_of_service',
        'employee_number',
        'mobile_number',
        'prc_license_number',
        'position',
        'career_stage',
        'subject',
        'grade_level',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Teacher has many observations
     */
    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class);
    }

    /**
     * Subjects handled by the teacher (many-to-many).
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects');
    }

    /**
     * Comma-separated list of the teacher's assigned subjects.
     * Falls back to the legacy free-text `subject` column so pre-existing
     * records keep displaying correctly even when not eager-loaded.
     */
    public function getSubjectsLabelAttribute(): ?string
    {
        if ($this->relationLoaded('subjects') && $this->subjects->isNotEmpty()) {
            return $this->subjects->pluck('name')->map('ucwords')->implode(', ');
        }

        return $this->subject ?: null;
    }

    /**
     * Canonical display labels for known teaching position codes
     * (e.g. `teacher_i` → "Teacher I").
     */
    public const POSITION_LABELS = [
        'teacher_i' => 'Teacher I',
        'teacher_ii' => 'Teacher II',
        'teacher_iii' => 'Teacher III',
        'master_teacher_i' => 'Master Teacher I',
        'master_teacher_ii' => 'Master Teacher II',
        'master_teacher_iii' => 'Master Teacher III',
        'master_teacher_iv' => 'Master Teacher IV',
        'master_teacher_v' => 'Master Teacher V',
        'special_education' => 'Special Education Teacher',
    ];

    /**
     * User-friendly label for a raw position value.
     * Returns null when the position is blank so callers can fall back.
     */
    public static function positionLabelFor(?string $position): ?string
    {
        if ($position === null || trim($position) === '') {
            return null;
        }

        $key = strtolower(trim($position));

        if (isset(self::POSITION_LABELS[$key])) {
            return self::POSITION_LABELS[$key];
        }

        // Generic fallback: "teacher_i" → "Teacher I" (roman numerals uppercased).
        $words = explode(' ', str_replace('_', ' ', $key));
        $words = array_map(function (string $word): string {
            return in_array($word, ['i', 'ii', 'iii', 'iv', 'v'], true)
                ? strtoupper($word)
                : ucfirst($word);
        }, $words);

        return implode(' ', $words);
    }

    /**
     * User-friendly label for this teacher's position.
     */
    public function getPositionLabelAttribute(): ?string
    {
        return self::positionLabelFor($this->position);
    }

    /**
     * User-friendly label for this teacher's grade level.
     */
    public function getGradeLevelLabelAttribute(): ?string
    {
        return \App\Enums\GradeLevel::labelFor($this->grade_level);
    }

    /**
     * The career stage resolved from the teacher's position, if any.
     */
    public function careerStage(): ?TeacherCareerStage
    {
        return $this->career_stage
            ? TeacherCareerStage::tryFrom($this->career_stage)
            : null;
    }

    /**
     * Normalise the current free-text position into a career stage.
     * Returns the stage (or null) without persisting it.
     */
    public function inferCareerStage(): ?TeacherCareerStage
    {
        return TeacherCareerStage::fromPosition($this->position);
    }

    /**
     * Normalise and persist the career stage derived from the current position.
     */
    public function normalizeCareerStage(): ?TeacherCareerStage
    {
        $stage = $this->inferCareerStage();

        if ($stage !== null && $this->career_stage !== $stage->value) {
            $this->career_stage = $stage->value;
            $this->save();
        }

        return $stage;
    }
}
