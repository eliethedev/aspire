<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'status',
        'password_set_at',
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

    /**
     * Get the supervisor profile associated with the user.
     */
    public function supervisor()
    {
        return $this->hasOne(Supervisor::class);
    }

    /**
     * Get the user profile associated with the user.
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the teacher profile associated with the user.
     */
    public function teacherProfile()
    {
        return $this->hasOne(TeacherProfile::class);
    }

    /**
     * Get the supervisor profile associated with the user.
     */
    public function supervisorProfile()
    {
        return $this->hasOne(SupervisorProfile::class);
    }

    /**
     * Get the school head profile associated with the user.
     */
    public function schoolHeadProfile()
    {
        return $this->hasOne(SchoolHeadProfile::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
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
