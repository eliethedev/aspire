<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Log an action related to invitations.
     */
    public function logInvitationAction(
        string $action,
        Invitation $invitation,
        User $actor,
        string $description = null,
        array $metadata = []
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $actor->id,
            'invitation_id' => $invitation->id,
            'action' => $action,
            'description' => $description,
            'metadata' => array_merge($metadata, [
                'invitation_email' => $invitation->email,
                'invitation_role' => $invitation->role,
                'school_id' => $invitation->school_id,
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log invitation creation.
     */
    public function logInvitationCreated(Invitation $invitation, User $actor): AuditLog
    {
        return $this->logInvitationAction(
            'invitation_created',
            $invitation,
            $actor,
            "Invitation sent to {$invitation->email} for role {$invitation->role}",
            [
                'expires_at' => $invitation->expires_at->toIso8601String(),
            ]
        );
    }

    /**
     * Log invitation acceptance.
     */
    public function logInvitationAccepted(Invitation $invitation, User $user): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user->id,
            'invitation_id' => $invitation->id,
            'action' => 'invitation_accepted',
            'description' => "User {$user->email} accepted invitation",
            'metadata' => [
                'invitation_email' => $invitation->email,
                'invitation_role' => $invitation->role,
                'school_id' => $invitation->school_id,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log invitation resend.
     */
    public function logInvitationResent(Invitation $invitation, User $actor): AuditLog
    {
        return $this->logInvitationAction(
            'invitation_resent',
            $invitation,
            $actor,
            "Invitation resent to {$invitation->email}",
            [
                'resend_count' => $invitation->resend_count,
                'expires_at' => $invitation->expires_at->toIso8601String(),
            ]
        );
    }

    /**
     * Log invitation cancellation.
     */
    public function logInvitationCancelled(Invitation $invitation, User $actor): AuditLog
    {
        return $this->logInvitationAction(
            'invitation_cancelled',
            $invitation,
            $actor,
            "Invitation for {$invitation->email} was cancelled"
        );
    }

    /**
     * Log user login.
     */
    public function logUserLogin(User $user): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user->id,
            'action' => 'user_login',
            'description' => "User {$user->email} logged in",
            'metadata' => [
                'user_role' => $user->role,
                'user_status' => $user->status,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get audit logs for a specific invitation.
     */
    public function getInvitationLogs(Invitation $invitation)
    {
        return AuditLog::forInvitation($invitation->id)
            ->with(['user'])
            ->latest()
            ->get();
    }

    /**
     * Get audit logs for a specific user.
     */
    public function getUserLogs(User $user, int $limit = 50)
    {
        return AuditLog::forUser($user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent audit logs.
     */
    public function getRecentLogs(int $days = 30, int $limit = 100)
    {
        return AuditLog::recent($days)
            ->with(['user', 'invitation'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
