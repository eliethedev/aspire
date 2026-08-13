<?php

namespace App\Enums;

enum NotificationPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    /**
     * Human-readable label. Always rendered as text so priority is never
     * communicated through color alone (important for older users).
     */
    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low priority',
            self::MEDIUM => 'Medium priority',
            self::HIGH => 'High priority',
            self::CRITICAL => 'Critical priority',
        };
    }

    /**
     * Sort order used when ordering notifications by priority.
     */
    public function rank(): int
    {
        return match ($this) {
            self::LOW => 0,
            self::MEDIUM => 1,
            self::HIGH => 2,
            self::CRITICAL => 3,
        };
    }
}
