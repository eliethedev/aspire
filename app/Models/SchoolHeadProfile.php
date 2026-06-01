<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolHeadProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_id',
        'position_level',
        'administrative_experience_years',
        'leadership_training',
        'current_designation',
        'number_of_teachers_supervised',
        'school_type',
        'additional_roles',
        'position',
    ];

    protected $casts = [
        'administrative_experience_years' => 'integer',
        'number_of_teachers_supervised' => 'integer',
    ];

    /**
     * Get the user that owns the school head profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the school that owns the school head profile.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the position level label.
     */
    public function getPositionLevelLabelAttribute(): string
    {
        return match($this->position_level) {
            'principal_i' => 'Principal I',
            'principal_ii' => 'Principal II',
            'principal_iii' => 'Principal III',
            'principal_iv' => 'Principal IV',
            'head_teacher' => 'Head Teacher',
            'assistant_principal' => 'Assistant Principal',
            default => $this->position_level,
        };
    }

    /**
     * Get the current designation label.
     */
    public function getCurrentDesignationLabelAttribute(): string
    {
        return match($this->current_designation) {
            'principal' => 'Principal',
            'officer_in_charge' => 'Officer-in-Charge',
            'head_teacher' => 'Head Teacher',
            'assistant_principal' => 'Assistant Principal',
            default => $this->current_designation,
        };
    }
}
