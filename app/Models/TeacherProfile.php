<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'grade_level',
        'subject_area_taught',
        'teaching_position',
        'strand_specialization',
        'has_advisory_class',
        'advisory_section',
        'teacher_load',
        'certification_training',
        'department',
        'default_room',
    ];

    protected $casts = [
        'has_advisory_class' => 'boolean',
        'teacher_load' => 'integer',
    ];

    /**
     * Get the user that owns the teacher profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the teaching position label.
     */
    public function getTeachingPositionLabelAttribute(): string
    {
        return str_replace('_', ' ', ucwords(str_replace('_', ' ', $this->teaching_position)));
    }

    /**
     * Get the grade level label.
     */
    public function getGradeLevelLabelAttribute(): string
    {
        return match($this->grade_level) {
            'elementary' => 'Elementary',
            'junior_high' => 'Junior High School',
            'senior_high' => 'Senior High School',
            default => $this->grade_level,
        };
    }
}
