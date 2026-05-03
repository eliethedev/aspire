<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Services\PHPMailerService;

class VerifyEmailPHPMailer extends Notification
{
    public function via($notifiable): array
    {
        return [\App\Mail\PHPMailerChannel::class];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        // Return a mail message for the channel to process
        // The actual sending will be handled by PHPMailerChannel
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('ASPIRE - Verify Your Email')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('If you did not create an account, no further action is required.');
    }

    public function verificationUrl($notifiable): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
