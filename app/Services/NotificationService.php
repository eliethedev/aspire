<?php

namespace App\Services;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\CareerAdvancement;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use InvalidArgumentException;

class NotificationService
{
    /**
     * Create a single notification.
     *
     * @param  NotificationType|string  $type  canonical type (enum or its ->value)
     * @param  NotificationPriority|string|null  $priority  defaults to the type's default priority
     * @param  string|null  $actionUrl  where clicking the notification navigates the user
     */
    public function notify(
        User $user,
        NotificationType|string $type,
        string $title,
        string $message,
        NotificationPriority|string|null $priority = null,
        ?string $actionUrl = null,
    ): Notification {
        $typeEnum = $type instanceof NotificationType
            ? $type
            : NotificationType::tryFrom($type);

        if (! $typeEnum) {
            throw new InvalidArgumentException("Unknown notification type [{$type}].");
        }

        $priorityEnum = match (true) {
            $priority === null => $typeEnum->defaultPriority(),
            $priority instanceof NotificationPriority => $priority,
            default => NotificationPriority::tryFrom($priority),
        };

        if (! $priorityEnum) {
            throw new InvalidArgumentException("Unknown notification priority [{$priority}].");
        }

        return Notification::create([
            'user_id' => $user->id,
            'type' => $typeEnum->value,
            'priority' => $priorityEnum->value,
            'title' => $title,
            'message' => $message,
            'link' => $actionUrl,
            'is_read' => false,
        ]);
    }

    /**
     * Notify many users at once.
     */
    public function notifyUsers(
        iterable $users,
        NotificationType|string $type,
        string $title,
        string $message,
        NotificationPriority|string|null $priority = null,
        ?string $actionUrl = null,
    ): SupportCollection {
        return collect($users)
            ->map(fn (User $user) => $this->notify($user, $type, $title, $message, $priority, $actionUrl));
    }

    /**
     * Notify every user holding the given role.
     */
    public function notifyByRole(
        string $role,
        NotificationType|string $type,
        string $title,
        string $message,
        NotificationPriority|string|null $priority = null,
        ?string $actionUrl = null,
    ): SupportCollection {
        return $this->notifyUsers(
            User::query()->where('role', $role)->get(),
            $type,
            $title,
            $message,
            $priority,
            $actionUrl
        );
    }

    /**
     * Legacy wrapper kept for existing call sites (e.g. AnnouncementController).
     */
    public function createNotification(User $user, string $type, string $title, string $message, ?string $link = null): Notification
    {
        return $this->notify($user, $type, $title, $message, null, $link);
    }

    /*
    |--------------------------------------------------------------------------
    | Convenience wrappers used across the app
    |--------------------------------------------------------------------------
    */

    public function notifyObservationScheduled(User $teacher, string $date, string $observationLink, ?string $time = null, ?string $location = null): void
    {
        $message = "Your observation has been scheduled for {$date}.";

        if ($time) {
            $message .= " Time: {$time}.";
        }

        if ($location) {
            $message .= " Location: {$location}.";
        }

        $this->notify(
            $teacher,
            NotificationType::OBSERVATION,
            'Observation Scheduled',
            $message,
            null,
            $observationLink
        );
    }

    public function notifyObservationCompleted(User $teacher, string $observationLink): void
    {
        $this->notify(
            $teacher,
            NotificationType::OBSERVATION_COMPLETED,
            'Observation Completed',
            'Your classroom observation has been completed. View the feedback.',
            null,
            $observationLink
        );
    }

    public function notifyFeedbackReceived(User $teacher, string $feedbackLink): void
    {
        $this->notify(
            $teacher,
            NotificationType::FEEDBACK,
            'New Feedback Received',
            'You have received new feedback on your observation.',
            null,
            $feedbackLink
        );
    }

    public function notifyObservationCancelled(User $observee, string $observationLink): void
    {
        $this->notify(
            $observee,
            NotificationType::OBSERVATION,
            'Observation Cancelled',
            'Your classroom observation has been cancelled. Please contact your supervisor for details.',
            null,
            $observationLink
        );
    }

    public function notifyLessonPlanUploaded(User $recipient, string $teacherName, string $observationLink): void
    {
        $this->notify(
            $recipient,
            NotificationType::LESSON_PLAN,
            'Lesson Plan Uploaded',
            "{$teacherName} has uploaded a lesson plan for review.",
            null,
            $observationLink
        );
    }

    public function notifyLessonPlanRequested(User $teacher, string $requesterName, string $observationLink): void
    {
        $this->notify(
            $teacher,
            NotificationType::LESSON_PLAN,
            'Lesson Plan Requested',
            "{$requesterName} has requested you to submit a lesson plan for an upcoming observation.",
            null,
            $observationLink
        );
    }

    public function notifyLessonPlanRequestedToSupervisor(User $supervisor, string $teacherName, string $observationLink): void
    {
        $this->notify(
            $supervisor,
            NotificationType::LESSON_PLAN,
            'Lesson Plan Requested',
            "You requested {$teacherName} to submit a lesson plan.",
            null,
            $observationLink
        );
    }

    public function notifyObservationConfirmed(User $supervisor, string $teacherName, string $observationLink): void
    {
        $this->notify(
            $supervisor,
            NotificationType::OBSERVATION,
            'Observation Confirmed',
            "{$teacherName} has confirmed the scheduled observation.",
            null,
            $observationLink
        );
    }

    public function notifyObservationRejected(User $supervisor, string $teacherName, string $reason, string $observationLink): void
    {
        $this->notify(
            $supervisor,
            NotificationType::OBSERVATION,
            'Observation Rejected',
            "{$teacherName} has rejected the scheduled observation. Reason: {$reason}.",
            null,
            $observationLink
        );
    }

    /**
     * Notify the ratee (teacher) and the school head(s) of the same school
     * whenever a career readiness assessment is saved or edited.
     */
    public function notifyCareerAssessment(User $ratee, ?string $schoolId, string $statusLabel, string $link, bool $edited = false): void
    {
        $action = $edited ? 'updated' : 'recorded';
        $title = $edited ? 'Career Readiness Assessment Updated' : 'Career Readiness Assessment';

        $this->notify(
            $ratee,
            NotificationType::ACHIEVEMENT,
            $title,
            "Your career readiness has been assessed as \"{$statusLabel}\".",
            null,
            $link
        );

        if ($schoolId) {
            $schoolHeads = \App\Models\User::query()
                ->where('role', 'school_head')
                ->where('school_id', $schoolId)
                ->get();

            foreach ($schoolHeads as $schoolHead) {
                $this->notify(
                    $schoolHead,
                    NotificationType::ACHIEVEMENT,
                    $title,
                    "Career readiness for {$ratee->name} has been {$action} as \"{$statusLabel}\".",
                    null,
                    $link
                );
            }
        }
    }

    /**
     * Notify the school head(s) of the same school that a supervisor has
     * recommended a career advancement which needs their review & approval.
     */
    public function notifyCareerAdvancementApprovalRequest(
        User $teacher,
        string $stageLabel,
        string $type,
        ?string $link = null,
    ): void {
        $schoolId = $teacher->school_id;

        if (! $schoolId) {
            return;
        }

        $verb = $type === CareerAdvancement::TYPE_ALLOW ? 'progress to the' : 'be announced as having achieved the';
        $title = 'Career advancement requires your approval';
        $message = "Supervisor has recommended that {$teacher->name} {$verb} {$stageLabel} career stage. Review and approve to complete the advancement.";

        $this->notify(
            $teacher,
            NotificationType::ACTION_REQUIRED,
            'Career advancement pending approval',
            "Your supervisor has recommended your advancement to the {$stageLabel} career stage. It is now awaiting school head approval.",
            null,
            null,
        );

        $schoolHeads = \App\Models\User::query()
            ->where('role', 'school_head')
            ->where('school_id', $schoolId)
            ->get();

        foreach ($schoolHeads as $schoolHead) {
            $this->notify(
                $schoolHead,
                NotificationType::ACTION_REQUIRED,
                $title,
                $message,
                null,
                $link,
            );
        }
    }

    /**
     * Notify the teacher that their career advancement was approved by the
     * school head (a congratulations-style achievement notification).
     */
    public function notifyCareerAdvancementApproved(
        User $teacher,
        string $stageLabel,
        ?string $link = null,
    ): void {
        $title = 'Congratulations on your career advancement!';
        $message = "Your career advancement to the {$stageLabel} career stage has been approved. Congratulations!";

        $this->notify(
            $teacher,
            NotificationType::ACHIEVEMENT,
            $title,
            $message,
            null,
            $link,
        );
    }

    /**
     * Notify the supervisor and the teacher that a career advancement was
     * rejected by the school head.
     */
    public function notifyCareerAdvancementRejected(
        User $supervisor,
        User $teacher,
        string $stageLabel,
        ?string $remarks = null,
    ): void {
        $title = 'Career advancement rejected';
        $message = "The school head did not approve {$this->advancementRejectedSubject($teacher->name)} the {$stageLabel} career stage."
            . ($remarks ? " Reason: {$remarks}" : '');

        $this->notify(
            $supervisor,
            NotificationType::SYSTEM,
            $title,
            $message,
            null,
            null,
        );

        $this->notify(
            $teacher,
            NotificationType::SYSTEM,
            'Career advancement not approved',
            "Your advancement to the {$stageLabel} career stage was not approved."
                . ($remarks ? " Reason: {$remarks}" : ''),
            null,
            null,
        );
    }

    private function advancementRejectedSubject(string $teacherName): string
    {
        return "{$teacherName}'s advancement to";
    }

    /*
    |--------------------------------------------------------------------------
    | Read / unread state
    |--------------------------------------------------------------------------
    */

    /**
     * Mark a single notification as read. Returns false when the notification
     * does not belong to the given user.
     */
    public function markAsRead(Notification $notification, User $user): bool
    {
        if (! $this->belongsTo($notification, $user)) {
            return false;
        }

        $notification->markAsRead();

        return true;
    }

    public function markAsUnread(Notification $notification, User $user): bool
    {
        if (! $this->belongsTo($notification, $user)) {
            return false;
        }

        $notification->markAsUnread();

        return true;
    }

    public function markAllAsRead(User $user): int
    {
        return $this->baseQuery($user)->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Delete a single notification. Returns false when the notification
     * does not belong to the given user.
     */
    public function delete(Notification $notification, User $user): bool
    {
        if (! $this->belongsTo($notification, $user)) {
            return false;
        }

        $notification->delete();

        return true;
    }

    public function unreadCount(User $user): int
    {
        return $this->baseQuery($user)->unread()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Retrieval
    |--------------------------------------------------------------------------
    */

    /**
     * Recent notifications for the header dropdown: unread first, then the
     * newest of the rest.
     */
    public function getRecent(User $user, int $limit = 8): SupportCollection
    {
        return $this->baseQuery($user)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getUnread(User $user, int $limit = 20): Collection
    {
        return $this->baseQuery($user)->unread()->latest()->limit($limit)->get();
    }

    /**
     * Paginated, filterable list for the "view all" page.
     */
    public function paginate(
        User $user,
        int $perPage = 15,
        string $status = 'all',
        ?NotificationType $type = null,
        ?NotificationPriority $priority = null,
    ): Paginator {
        $query = $this->baseQuery($user)->latest();

        if ($status === 'unread') {
            $query->unread();
        } elseif ($status === 'read') {
            $query->read();
        }

        if ($type) {
            $query->ofType($type);
        }

        if ($priority) {
            $query->ofPriority($priority);
        }

        return $query->paginate($perPage);
    }

    protected function belongsTo(Notification $notification, User $user): bool
    {
        return (int) $notification->user_id === (int) $user->id;
    }

    protected function baseQuery(User $user)
    {
        return Notification::query()->forUser($user);
    }
}
