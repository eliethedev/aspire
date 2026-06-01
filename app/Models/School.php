<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'subdomain',
        'settings',
        'is_active',
        'trial_ends_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function schoolUsers(): HasMany
    {
        return $this->hasMany(SchoolUser::class);
    }

    public function teachers(): HasMany
    {
        return $this->schoolUsers()->where('role', 'teacher');
    }

    public function supervisors(): HasMany
    {
        return $this->schoolUsers()->where('role', 'supervisor');
    }

    public function schoolHeads(): HasMany
    {
        return $this->schoolUsers()->where('role', 'school_head');
    }

    public function admins(): HasMany
    {
        return $this->schoolUsers()->where('role', 'admin');
    }

    public function isActive(): bool
    {
        return $this->is_active && (!$this->trial_ends_at || $this->trial_ends_at->isFuture());
    }

    public function getDomainAttribute(): string
    {
        return $this->attributes['domain'] ?? ($this->subdomain ? $this->subdomain . '.' . config('app.domain') : '');
    }
}
