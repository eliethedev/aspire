<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Services\PHPMailerService;

class VerifyEmailPHPMailer extends Notification
{
    public function __construct(
        protected ?string $code = null,
    ) {}

    public function via($notifiable): array
    {
        return [\App\Mail\PHPMailerChannel::class];
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function toMail($notifiable)
    {
        $code = $this->code ?? '';

        // Return a mail message for the channel to process
        // The actual sending will be handled by PHPMailerChannel
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('ASPIRE - Your Email Verification Code')
            ->line("Your ASPIRE email verification code is: {$code}")
            ->line('Enter this 6-digit code on the verification page to verify your email address. It expires in 30 minutes.')
            ->line('If you did not create an account, no further action is required.');
    }
}
