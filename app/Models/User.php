<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'school_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolUsers(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'school_users')
            ->withPivot('role', 'is_active')
            ->withTimestamps();
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    // Role-based methods
    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isSchoolHead(): bool
    {
        return $this->role === 'school_head';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    // School-aware role checking
    public function hasSchoolRole(School $school, string $role): bool
    {
        return $this->schoolUsers()
            ->wherePivot('school_id', $school->id)
            ->wherePivot('role', $role)
            ->wherePivot('is_active', true)
            ->exists();
    }

    public function canAccessSchool(School $school): bool
    {
        return $this->schoolUsers()
            ->wherePivot('school_id', $school->id)
            ->wherePivot('is_active', true)
            ->exists();
    }

    /**
     * Send the email verification notification using PHPMailer.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmailPHPMailer());
    }

    /**
     * Send the password reset notification using PHPMailer.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordPHPMailer($token));
    }
}
