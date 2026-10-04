<?php

namespace App\Notifications;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Dedicated password-reset email sent from the forgot-password flow.
 *
 * Unlike UserInvitation (for newly invited users joining ASPIRE), this
 * notification is for existing users resetting a forgotten password: the
 * subject, greeting and body never mention an invitation.
 */
class PasswordResetEmail extends Notification
{
    use Queueable;

    protected User $user;

    protected Invitation $invitation;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, Invitation $invitation)
    {
        $this->user = $user;
        $this->invitation = $invitation;
    }

    /**
     * Get the user requesting the reset.
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Get the reset invitation instance.
     */
    public function getInvitation(): Invitation
    {
        return $this->invitation;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [\App\Mail\PHPMailerChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = url('/auth/set-password/' . $this->invitation->token);
        $expiresAt = $this->invitation->expires_at->format('F j, Y \a\t g:i A');

        return (new MailMessage)
            ->subject('Reset Your ASPIRE Password')
            ->greeting('Hello ' . $this->user->name . ',')
            ->line('We received a request to reset the password for your ASPIRE account.')
            ->line('Click the button below to set a new password. This link expires on ' . $expiresAt . '.')
            ->action('Reset Password', $resetUrl)
            ->line('If you did not request a password reset, you can safely ignore this email — your current password will keep working.')
            ->salutation('Best regards,')
            ->line('The ASPIRE Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user->id,
            'invitation_id' => $this->invitation->id,
            'token' => $this->invitation->token,
            'expires_at' => $this->invitation->expires_at,
        ];
    }
}
