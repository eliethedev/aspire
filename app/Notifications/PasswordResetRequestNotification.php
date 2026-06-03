<?php

namespace App\Notifications;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetRequestNotification extends Notification
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
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', \App\Mail\PHPMailerChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Password Reset Request: ' . $this->user->email)
            ->greeting('Hello Admin,')
            ->line('A user has requested a password reset via the forgot password feature.')
            ->line('**User Email:** ' . $this->user->email)
            ->line('**User Name:** ' . $this->user->name)
            ->line('**User Role:** ' . ucfirst($this->user->role))
            ->line('**School:** ' . ($this->user->school?->name ?? 'Not assigned'))
            ->line('**Request Time:** ' . now()->format('F j, Y \a\t g:i A'))
            ->line('**IP Address:** ' . $this->invitation->ip_address)
            ->line('A new invitation has been sent to the user to reset their password.')
            ->line('If this request seems suspicious, please investigate the user account.')
            ->action('View User', url('/admin/users/' . $this->user->id))
            ->salutation('Best regards,')
            ->line('The ASPIRE System');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user->id,
            'user_email' => $this->user->email,
            'user_name' => $this->user->name,
            'user_role' => $this->user->role,
            'invitation_id' => $this->invitation->id,
            'ip_address' => $this->invitation->ip_address,
            'message' => $this->user->email . ' requested a password reset / new invitation.',
        ];
    }
}
