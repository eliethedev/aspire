<?php

namespace App\Enums;

/**
 * Centralized catalogue of in-app notification types.
 *
 * Store the enum's ->value (a plain string) in the database and never rely
 * on raw strings scattered across the application.
 */
enum NotificationType: string
{
    case OBSERVATION = 'observation';
    case OBSERVATION_COMPLETED = 'observation_completed';
    case FEEDBACK = 'feedback';
    case AI_SUGGESTION = 'ai_suggestion';
    case LESSON_PLAN = 'lesson_plan';
    case ACTION_REQUIRED = 'action_required';
    case CONFERENCE = 'conference';
    case REMINDER = 'reminder';
    case ACHIEVEMENT = 'achievement';
    case PROFESSIONAL_DEVELOPMENT = 'professional_development';
    case INVITATION = 'invitation';
    case SECURITY = 'security';
    case SYSTEM = 'system';
    case ANNOUNCEMENT = 'announcement';
    case SUPPORT_MESSAGE = 'support_message';

    /**
     * Human-readable label shown to users.
     */
    public function label(): string
    {
        return match ($this) {
            self::OBSERVATION => 'Observation',
            self::OBSERVATION_COMPLETED => 'Observation Completed',
            self::FEEDBACK => 'Feedback',
            self::AI_SUGGESTION => 'AI Suggestion',
            self::LESSON_PLAN => 'Lesson Plan',
            self::ACTION_REQUIRED => 'Action Required',
            self::CONFERENCE => 'Conference',
            self::REMINDER => 'Reminder',
            self::ACHIEVEMENT => 'Achievement',
            self::PROFESSIONAL_DEVELOPMENT => 'Professional Development',
            self::INVITATION => 'Invitation',
            self::SECURITY => 'Security',
            self::SYSTEM => 'System',
            self::ANNOUNCEMENT => 'Announcement',
            self::SUPPORT_MESSAGE => 'Support Message',
        };
    }

    /**
     * The priority a notification of this type should carry unless one is
     * supplied explicitly. Keeping the mapping here (rather than in Blade)
     * guarantees the same default everywhere.
     */
    public function defaultPriority(): NotificationPriority
    {
        return match ($this) {
            self::OBSERVATION,
            self::FEEDBACK,
            self::ACTION_REQUIRED,
            self::CONFERENCE,
            self::INVITATION => NotificationPriority::HIGH,

            self::SECURITY => NotificationPriority::CRITICAL,

            self::ACHIEVEMENT,
            self::SYSTEM => NotificationPriority::LOW,

            self::OBSERVATION_COMPLETED,
            self::AI_SUGGESTION,
            self::LESSON_PLAN,
            self::REMINDER,
            self::PROFESSIONAL_DEVELOPMENT,
            self::ANNOUNCEMENT,
            self::SUPPORT_MESSAGE => NotificationPriority::MEDIUM,
        };
    }
}
