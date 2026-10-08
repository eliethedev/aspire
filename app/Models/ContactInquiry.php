<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactInquiry extends Model
{
    use HasFactory;

    public const ROLE_TEACHER = 'teacher';
    public const ROLE_SCHOOL_HEAD = 'school_head';
    public const ROLE_SUPERVISOR = 'supervisor';
    public const ROLE_OTHER = 'other';

    public const TOPIC_ACCOUNT_ACCESS = 'account_access';
    public const TOPIC_DEMO = 'demo';
    public const TOPIC_PARTNERSHIP = 'partnership';
    public const TOPIC_FEEDBACK = 'feedback';
    public const TOPIC_OTHER = 'other';

    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';

    public const ROLES = [self::ROLE_TEACHER, self::ROLE_SCHOOL_HEAD, self::ROLE_SUPERVISOR, self::ROLE_OTHER];
    public const TOPICS = [self::TOPIC_ACCOUNT_ACCESS, self::TOPIC_DEMO, self::TOPIC_PARTNERSHIP, self::TOPIC_FEEDBACK, self::TOPIC_OTHER];
    public const STATUSES = [self::STATUS_NEW, self::STATUS_IN_PROGRESS, self::STATUS_RESOLVED];

    protected $fillable = [
        'name',
        'email',
        'role',
        'school_name',
        'topic',
        'subject',
        'message',
        'status',
        'admin_note',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeByTopic($query, string $topic)
    {
        return $query->where('topic', $topic);
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_TEACHER => 'Teacher',
            self::ROLE_SCHOOL_HEAD => 'School Head',
            self::ROLE_SUPERVISOR => 'Supervisor',
            default => 'Other',
        };
    }

    public function topicLabel(): string
    {
        return match ($this->topic) {
            self::TOPIC_ACCOUNT_ACCESS => 'Account access',
            self::TOPIC_DEMO => 'Request a demo',
            self::TOPIC_PARTNERSHIP => 'Partnership',
            self::TOPIC_FEEDBACK => 'Feedback',
            default => 'Other',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_RESOLVED => 'Resolved',
            default => 'New',
        };
    }
}
