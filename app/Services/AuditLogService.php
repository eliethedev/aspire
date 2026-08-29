<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    public function log(
        string $action,
        string $module = null,
        string $recordId = null,
        string $description = null,
        string $status = 'success',
        array $oldValues = [],
        array $newValues = [],
        array $metadata = [],
        ?User $actor = null,
    ): AuditLog {
        $actor ??= request()?->user();
        $role = $actor?->role ?? 'system';

        return AuditLog::create([
            'user_id' => $actor?->id,
            'role' => $role,
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'description' => $description,
            'status' => $status,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    public function logCreate(
        string $module,
        Model $record,
        string $description = null,
        ?User $actor = null,
    ): AuditLog {
        return $this->log(
            'created',
            $module,
            (string) $record->getKey(),
            $description ?? "Created {$module}: #{$record->getKey()}",
            'success',
            [],
            $record->toArray(),
            [],
            $actor,
        );
    }

    public function logUpdate(
        string $module,
        Model $record,
        array $oldValues,
        array $newValues,
        string $description = null,
        ?User $actor = null,
    ): AuditLog {
        return $this->log(
            'updated',
            $module,
            (string) $record->getKey(),
            $description ?? "Updated {$module}: #{$record->getKey()}",
            'success',
            $oldValues,
            $newValues,
            [],
            $actor,
        );
    }

    public function logDelete(
        string $module,
        Model $record,
        string $description = null,
        ?User $actor = null,
    ): AuditLog {
        return $this->log(
            'deleted',
            $module,
            (string) $record->getKey(),
            $description ?? "Deleted {$module}: #{$record->getKey()}",
            'success',
            $record->toArray(),
            [],
            [],
            $actor,
        );
    }

    public function logAuth(
        User $user,
        string $action,
        bool $success = true,
        string $reason = null,
    ): AuditLog {
        return $this->log(
            $action,
            'auth',
            (string) $user->getKey(),
            $reason ?? ($success ? "User {$user->email} logged in" : "Failed login for {$user->email}"),
            $success ? 'success' : 'failed',
            [],
            ['user_role' => $user->role, 'user_status' => $user->status],
            ['reason' => $reason],
            $user,
        );
    }

    public function logInvitationAction(
        string $action,
        Invitation $invitation,
        User $actor,
        string $description = null,
        array $metadata = []
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $actor->id,
            'role' => $actor->role,
            'invitation_id' => $invitation->id,
            'action' => $action,
            'module' => 'invitations',
            'record_id' => (string) $invitation->getKey(),
            'description' => $description,
            'status' => 'success',
            'metadata' => array_merge($metadata, [
                'invitation_email' => $invitation->email,
                'invitation_role' => $invitation->role,
                'school_id' => $invitation->school_id,
            ]),
            'old_values' => [],
            'new_values' => $invitation->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function logInvitationCreated(Invitation $invitation, User $actor): AuditLog
    {
        return $this->logInvitationAction(
            'invitation_created',
            $invitation,
            $actor,
            "Invitation sent to {$invitation->email} for role {$invitation->role}",
            ['expires_at' => $invitation->expires_at->toIso8601String()],
        );
    }

    public function logInvitationAccepted(Invitation $invitation, User $user): AuditLog
    {
        return $this->log(
            'invitation_accepted',
            'invitations',
            (string) $invitation->getKey(),
            "User {$user->email} accepted invitation",
            'success',
            [],
            $invitation->toArray(),
            [
                'invitation_email' => $invitation->email,
                'invitation_role' => $invitation->role,
                'school_id' => $invitation->school_id,
            ],
            $user,
        );
    }

    public function logInvitationResent(Invitation $invitation, User $actor): AuditLog
    {
        return $this->logInvitationAction(
            'invitation_resent',
            $invitation,
            $actor,
            "Invitation resent to {$invitation->email}",
            ['resend_count' => $invitation->resend_count, 'expires_at' => $invitation->expires_at->toIso8601String()],
        );
    }

    public function logInvitationCancelled(Invitation $invitation, User $actor): AuditLog
    {
        return $this->logInvitationAction(
            'invitation_cancelled',
            $invitation,
            $actor,
            "Invitation for {$invitation->email} was cancelled",
        );
    }

    public function logUserLogin(User $user): AuditLog
    {
        return $this->logAuth($user, 'user_login', true);
    }

    public function logFailedLogin(User $user = null, string $email = null, string $reason = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user?->id,
            'role' => $user?->role ?? 'guest',
            'action' => 'user_login_failed',
            'module' => 'auth',
            'description' => $reason ?? "Failed login attempt for {$email}",
            'status' => 'failed',
            'metadata' => ['email' => $email, 'reason' => $reason],
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    public function logUserLogout(User $user): AuditLog
    {
        return $this->logAuth($user, 'user_logout', true, "User {$user->email} logged out");
    }

    public function getInvitationLogs(Invitation $invitation)
    {
        return AuditLog::forInvitation($invitation->id)
            ->with(['user'])
            ->latest()
            ->get();
    }

    public function getUserLogs(User $user, int $limit = 50)
    {
        return AuditLog::forUser($user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function logAi(
        string $action,
        string $description = null,
        string $recordId = null,
        string $status = 'success',
        array $metadata = [],
    ): AuditLog {
        return $this->log(
            $action,
            'ai',
            $recordId,
            $description,
            $status,
            [],
            [],
            $metadata,
        );
    }

    public function getRecentLogs(int $days = 30, int $limit = 100)
    {
        return AuditLog::recent($days)
            ->with(['user'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
