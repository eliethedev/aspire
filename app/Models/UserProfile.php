<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mobile_number',
        'date_of_birth',
        'gender',
        'address_barangay',
        'address_municipality',
        'address_province',
        'employee_id',
        'prc_license_number',
        'highest_educational_attainment',
        'major_specialization',
        'years_of_teaching_experience',
        'date_of_entry_to_deped',
        'employment_status',
        'office_department',
        'position_title',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_entry_to_deped' => 'date',
        'years_of_teaching_experience' => 'integer',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full address as a formatted string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_barangay,
            $this->address_municipality,
            $this->address_province,
        ]);

        return implode(', ', $parts);
    }
}
