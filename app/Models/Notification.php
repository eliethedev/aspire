<?php

namespace App\Models;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'priority',
        'title',
        'message',
        'link',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'priority' => NotificationPriority::class,
    ];

    /**
     * The type is intentionally left as a plain string in the database for
     * forward compatibility; use the enum helpers below when you need an
     * actual NotificationType instance.
     */
    public function typeEnum(): ?NotificationType
    {
        return NotificationType::tryFrom($this->type);
    }

    public function typeLabel(): string
    {
        return $this->typeEnum()?->label() ?? $this->type;
    }

    public function priorityEnum(): ?NotificationPriority
    {
        if ($this->priority instanceof NotificationPriority) {
            return $this->priority;
        }

        return NotificationPriority::tryFrom((string) $this->priority);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRead(): bool
    {
        return (bool) $this->is_read;
    }

    public function isUnread(): bool
    {
        return ! $this->isRead();
    }

    public function markAsRead(): void
    {
        if ($this->isRead()) {
            return;
        }

        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread(): void
    {
        if ($this->isUnread()) {
            return;
        }

        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    public function scopeForUser(Builder $query, int|User $user): Builder
    {
        return $query->where('user_id', $user instanceof User ? $user->id : $user);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }

    public function scopeOfType(Builder $query, NotificationType|string $type): Builder
    {
        return $query->where('type', $type instanceof NotificationType ? $type->value : $type);
    }

    public function scopeOfPriority(Builder $query, NotificationPriority|string $priority): Builder
    {
        return $query->where('priority', $priority instanceof NotificationPriority ? $priority->value : $priority);
    }

    /**
     * Most recent first.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->latest('created_at');
    }
}
