<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'status',
        'sent_by',
        'sent_at',
        'target_roles',
        'link',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'sent_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function scopeDrafts($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function markAsSent(): void
    {
        $this->update(['status' => 'sent', 'sent_at' => now()]);
    }

    public function targetsAll(): bool
    {
        return empty($this->target_roles);
    }

    public function targetsRole(string $role): bool
    {
        return $this->targetsAll() || in_array($role, $this->target_roles ?? []);
    }
}
