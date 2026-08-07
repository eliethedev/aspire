<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInvitation extends Notification
{
    use Queueable;

    protected Invitation $invitation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the invitation instance.
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
        $setPasswordUrl = url('/auth/set-password/' . $this->invitation->token);
        $expiresAt = $this->invitation->expires_at->format('F j, Y \a\t g:i A');

        return (new MailMessage)
            ->subject('You\'re Invited to Join ASPIRE')
            ->greeting('Hello ' . $this->invitation->user->name . ',')
            ->line('You have been invited to join ASPIRE, the Department of Education\'s school supervision platform.')
            ->line('**Your Role:** ' . ucfirst($this->invitation->role))
            ->line('**School:** ' . ($this->invitation->school?->name ?? 'Not assigned'))
            ->line('**Invited by:** ' . ($this->invitation->invitedBy?->name ?? 'The Administrator'))
            ->line('**Expires:** ' . $expiresAt)
            ->line('To get started, please click the button below to set your password and activate your account.')
            ->action('Set Your Password', $setPasswordUrl)
            ->line('This invitation link will expire in 7 days. If you don\'t set your password before then, you\'ll need to request a new invitation.')
            ->line('If you did not expect this invitation, you can safely ignore this email.')
            ->salutation('Best regards,')
            ->line('The ASPIRE Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'token' => $this->invitation->token,
            'role' => $this->invitation->role,
            'expires_at' => $this->invitation->expires_at,
        ];
    }
}
