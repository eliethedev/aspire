<?php

namespace App\Mail;

use Illuminate\Notifications\Notification;
use App\Services\PHPMailerService;

class PHPMailerChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toMail($notifiable);

        try {
            $mailerService = app(PHPMailerService::class);
            
            // For verification emails, use the dedicated method
            if ($notification instanceof \App\Notifications\VerifyEmailPHPMailer) {
                $verificationUrl = $notification->verificationUrl($notifiable);
                return $mailerService->sendVerificationEmail($notifiable, $verificationUrl);
            }
            
            // For password reset emails
            if ($notification instanceof \Illuminate\Auth\Notifications\ResetPassword) {
                $resetUrl = $message->actionUrl;
                return $mailerService->sendPasswordResetEmail($notifiable, $resetUrl);
            }

            // For other emails, use a generic method
            return $this->sendGenericEmail($notifiable, $message);

        } catch (\Exception $e) {
            \Log::error('PHPMailer sending failed: ' . $e->getMessage());
            return false;
        }
    }

    private function sendGenericEmail($notifiable, $message): bool
    {
        try {
            $mailerService = app(PHPMailerService::class);
            
            $mailerService->mailer->addAddress($notifiable->email, $notifiable->name);
            $mailerService->mailer->Subject = $message->subject;
            $mailerService->mailer->Body = $this->buildGenericEmail($message);
            $mailerService->mailer->AltBody = strip_tags($mailerService->mailer->Body);

            return $mailerService->mailer->send();

        } catch (\Exception $e) {
            \Log::error('Failed to send generic email: ' . $e->getMessage());
            return false;
        }
    }

    private function buildGenericEmail($message): string
    {
        $content = '';
        
        foreach ($message->introLines as $line) {
            $content .= '<p>' . htmlspecialchars($line) . '</p>';
        }

        if ($message->actionUrl && $message->actionText) {
            $content .= "
                <div style='text-align: center; margin: 20px 0;'>
                    <a href='{$message->actionUrl}' style='display: inline-block; padding: 12px 24px; background: #1e40af; color: white; text-decoration: none; border-radius: 4px;'>
                        {$message->actionText}
                    </a>
                </div>
            ";
        }

        foreach ($message->outroLines as $line) {
            $content .= '<p>' . htmlspecialchars($line) . '</p>';
        }

        return $this->wrapEmailTemplate($content, $message->subject);
    }

    private function wrapEmailTemplate($content, $subject): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>{$subject}</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #1e40af; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9fafb; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🏫 ASPIRE System</h1>
                    <p>Automated Supervision Platform for Instructional Reform & Excellence</p>
                </div>
                <div class='content'>
                    <h2>{$subject}</h2>
                    {$content}
                </div>
                <div class='footer'>
                    <p>This is an automated message from the ASPIRE system. Please do not reply to this email.</p>
                    <p>© 2026 ASPIRE - Department of Education Philippines</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
