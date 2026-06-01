<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'division_district_assigned',
        'area_of_specialization',
        'supervisory_level',
        'previous_teaching_experience_years',
        'administrative_experience_years',
        'key_responsibilities',
        'position',
    ];

    protected $casts = [
        'previous_teaching_experience_years' => 'integer',
        'administrative_experience_years' => 'integer',
    ];

    /**
     * Get the user that owns the supervisor profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the supervisory level label.
     */
    public function getSupervisoryLevelLabelAttribute(): string
    {
        return match($this->supervisory_level) {
            'division' => 'Division Level',
            'district' => 'District Level',
            'regional' => 'Regional Level',
            default => $this->supervisory_level,
        };
    }
}
