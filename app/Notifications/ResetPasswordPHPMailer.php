<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Notification;
use App\Services\PHPMailerService;

class ResetPasswordPHPMailer extends ResetPassword
{
    public function via($notifiable): array
    {
        return [\App\Mail\PHPMailerChannel::class];
    }

    public function toMail($notifiable)
    {
        $resetUrl = $this->resetUrl($notifiable);

        try {
            // The actual email is sent by PHPMailerChannel (deferred, without
            // blocking the HTTP request). Here we only build the message shell.
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Reset Password Notification')
                ->line('You are receiving this email because we received a password reset request for your account.')
                ->action('Reset Password', $resetUrl)
                ->line('This password reset link will expire in ' . config('auth.passwords.users.expire', 60) . ' minutes.')
                ->line('If you did not request a password reset, no further action is required.');

        } catch (\Exception $e) {
            \Log::error('Password reset email error: ' . $e->getMessage());

            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Reset Password Notification')
                ->line('There was an issue sending the password reset email. Please contact support.')
                ->line('Error: ' . $e->getMessage());
        }
    }

    protected function resetUrl($notifiable): string
    {
        return route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
