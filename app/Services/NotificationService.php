<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function createNotification(User $user, string $type, string $title, string $message, ?string $link = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => false,
        ]);
    }

    public function notifyUserInvitation(User $user, string $invitationLink): void
    {
        $this->createNotification(
            $user,
            'user_invitation',
            'New User Invitation',
            'You have been invited to join the ASPIRE platform.',
            $invitationLink
        );
    }

    public function notifySchoolCreated(User $admin, string $schoolName, string $schoolLink): void
    {
        $this->createNotification(
            $admin,
            'school_created',
            'School Created',
            "School '{$schoolName}' has been successfully created.",
            $schoolLink
        );
    }

    public function notifyObservationAssigned(User $supervisor, string $teacherName, string $observationLink): void
    {
        $this->createNotification(
            $supervisor,
            'observation_assigned',
            'Observation Assigned',
            "You have been assigned to observe teacher {$teacherName}.",
            $observationLink
        );
    }

    public function notifyObservationScheduled(User $teacher, string $date, string $observationLink): void
    {
        $this->createNotification(
            $teacher,
            'observation_scheduled',
            'Observation Scheduled',
            "Your classroom observation has been scheduled for {$date}.",
            $observationLink
        );
    }

    public function notifyObservationCompleted(User $teacher, string $observationLink): void
    {
        $this->createNotification(
            $teacher,
            'observation_completed',
            'Observation Completed',
            'Your classroom observation has been completed. View the feedback.',
            $observationLink
        );
    }

    public function notifyFeedbackReceived(User $teacher, string $feedbackLink): void
    {
        $this->createNotification(
            $teacher,
            'feedback_received',
            'New Feedback Received',
            'You have received new feedback on your observation.',
            $feedbackLink
        );
    }

    public function notifyPreConferenceScheduled(User $supervisor, string $date, string $conferenceLink): void
    {
        $this->createNotification(
            $supervisor,
            'pre_conference_scheduled',
            'Pre-Conference Scheduled',
            "Pre-conference meeting scheduled for {$date}.",
            $conferenceLink
        );
    }

    public function notifyPostConferenceScheduled(User $supervisor, string $date, string $conferenceLink): void
    {
        $this->createNotification(
            $supervisor,
            'post_conference_scheduled',
            'Post-Conference Scheduled',
            "Post-conference meeting scheduled for {$date}.",
            $conferenceLink
        );
    }

    public function notifyObservationReady(User $supervisor, string $observationLink): void
    {
        $this->createNotification(
            $supervisor,
            'observation_ready',
            'Observation Ready for Review',
            'The observation is ready for your review.',
            $observationLink
        );
    }

    public function notifyTeacherAdded(User $schoolHead, string $teacherName, string $teacherLink): void
    {
        $this->createNotification(
            $schoolHead,
            'teacher_added',
            'Teacher Added',
            "Teacher {$teacherName} has been added to your school.",
            $teacherLink
        );
    }

    public function notifyObservationReport(User $schoolHead, string $reportLink): void
    {
        $this->createNotification(
            $schoolHead,
            'observation_report',
            'New Observation Report',
            'A new observation report is available for review.',
            $reportLink
        );
    }

    public function notifySchoolUpdate(User $schoolHead, string $updateMessage, ?string $link = null): void
    {
        $this->createNotification(
            $schoolHead,
            'school_update',
            'School Update',
            $updateMessage,
            $link
        );
    }

    public function notifyObservationCancelled(User $observee, string $observationLink): void
    {
        $this->createNotification(
            $observee,
            'observation_cancelled',
            'Observation Cancelled',
            'Your classroom observation has been cancelled. Please contact your supervisor for details.',
            $observationLink
        );
    }

    public function notifyLessonPlanUploaded(User $recipient, string $teacherName, string $observationLink): void
    {
        $this->createNotification(
            $recipient,
            'lesson_plan_uploaded',
            'Lesson Plan Uploaded',
            "{$teacherName} has uploaded a lesson plan for review.",
            $observationLink
        );
    }

    public function notifyLessonPlanRequested(User $teacher, string $requesterName, string $observationLink): void
    {
        $this->createNotification(
            $teacher,
            'lesson_plan_requested',
            'Lesson Plan Requested',
            "{$requesterName} has requested you to submit a lesson plan for an upcoming observation.",
            $observationLink
        );
    }

    public function notifyLessonPlanRequestedToSupervisor(User $supervisor, string $teacherName, string $observationLink): void
    {
        $this->createNotification(
            $supervisor,
            'lesson_plan_requested',
            'Lesson Plan Requested',
            "You requested {$teacherName} to submit a lesson plan.",
            $observationLink
        );
    }

    public function notifyObservationConfirmed(User $supervisor, string $teacherName, string $observationLink): void
    {
        $this->createNotification(
            $supervisor,
            'observation_confirmed',
            'Observation Confirmed',
            "{$teacherName} has confirmed the scheduled observation.",
            $observationLink
        );
    }

    public function notifyObservationRejected(User $supervisor, string $teacherName, string $reason, string $observationLink): void
    {
        $this->createNotification(
            $supervisor,
            'observation_rejected',
            'Observation Rejected',
            "{$teacherName} has rejected the scheduled observation. Reason: {$reason}.",
            $observationLink
        );
    }
}
