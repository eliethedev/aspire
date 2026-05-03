<?php

namespace App\Models;

use App\Models\Traits\SchoolAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
