<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'email_verification_code',
        'email_verification_expires_at',
        'password',
        'role',
        'school_id',
        'status',
        'password_set_at',
        'terms_accepted_at',
        'settings',
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
            'email_verification_expires_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
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

    public function supportMessages(): HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }

    /*
    |--------------------------------------------------------------------------
    | UI preferences (stored in the users.settings JSON column)
    |--------------------------------------------------------------------------
    */

    public function observationTipDismissed(): bool
    {
        return (bool) ($this->settings['observation_tip_dismissed'] ?? false);
    }

    public function dismissObservationTip(bool $dismissed = true): void
    {
        $settings = $this->settings ?? [];
        $settings['observation_tip_dismissed'] = $dismissed;
        $this->update(['settings' => $settings]);
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
        $code = $this->generateEmailVerificationCode();

        $this->notify(new \App\Notifications\VerifyEmailPHPMailer($code));
    }

    /**
     * Generate a fresh 6-digit email verification code.
     *
     * The code is stored hashed with a 30-minute expiry. Returns the
     * plain-text code so it can be included in the verification email.
     */
    public function generateEmailVerificationCode(int $validMinutes = 30): string
    {
        $code = (string) random_int(100000, 999999);

        $this->forceFill([
            'email_verification_code' => \Illuminate\Support\Facades\Hash::make($code),
            'email_verification_expires_at' => now()->addMinutes($validMinutes),
        ])->save();

        return $code;
    }

    /**
     * Check a submitted 6-digit code against the stored hash and expiry.
     */
    public function hasValidEmailVerificationCode(string $code): bool
    {
        if (empty($this->email_verification_code)) {
            return false;
        }

        if ($this->email_verification_expires_at && $this->email_verification_expires_at->isPast()) {
            return false;
        }

        return \Illuminate\Support\Facades\Hash::check($code, $this->email_verification_code);
    }

    /**
     * Discard the verification code (e.g. after successful verification).
     */
    public function clearEmailVerificationCode(): void
    {
        $this->forceFill([
            'email_verification_code' => null,
            'email_verification_expires_at' => null,
        ])->save();
    }

    /**
     * Send the password reset notification using PHPMailer.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordPHPMailer($token));
    }
}
