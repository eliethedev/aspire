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

            // For invitation emails, use the dedicated method
            if ($notification instanceof \App\Notifications\UserInvitation) {
                return $this->sendInvitationEmail($notifiable, $message, $notification);
            }

            // For other emails, use a generic method
            return $this->sendGenericEmail($notifiable, $message);

        } catch (\Exception $e) {
            \Log::error('PHPMailer sending failed: ' . $e->getMessage());
            return false;
        }
    }

    private function sendInvitationEmail($notifiable, $message, $notification): bool
    {
        try {
            $mailerService = app(PHPMailerService::class);
            $invitation = $notification->getInvitation();

            $body = $this->buildInvitationEmail($message, $invitation);

            return $mailerService->sendInvitationEmail($invitation, $message->subject, $body);

        } catch (\Exception $e) {
            \Log::error('Failed to send invitation email: ' . $e->getMessage());
            return false;
        }
    }

    private function buildInvitationEmail($message, $invitation): string
    {
        $setPasswordUrl = url('/auth/set-password/' . $invitation->token);
        $expiresAt = $invitation->expires_at->format('F j, Y \a\t g:i A');

        $content = "
            <p>Hello {$invitation->user->name},</p>
            <p>You have been invited to join ASPIRE, the Department of Education's school supervision platform.</p>
            <div style='background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                <p><strong>Your Role:</strong> " . ucfirst($invitation->role) . "</p>
                <p><strong>School:</strong> " . ($invitation->school?->name ?? 'Not assigned') . "</p>
                <p><strong>Invited by:</strong> {$invitation->invitedBy->name}</p>
                <p><strong>Expires:</strong> {$expiresAt}</p>
            </div>
            <p>To get started, please click the button below to set your password and activate your account.</p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$setPasswordUrl}' style='display: inline-block; padding: 14px 28px; background: #1e40af; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;'>
                    Set Your Password
                </a>
            </div>
            <p>This invitation link will expire in 7 days. If you don't set your password before then, you'll need to request a new invitation.</p>
            <p>If you did not expect this invitation, you can safely ignore this email.</p>
            <p>Best regards,<br>The ASPIRE Team</p>
        ";

        return $this->wrapEmailTemplate($content, $message->subject);
    }

    private function sendGenericEmail($notifiable, $message): bool
    {
        try {
            $mailerService = app(PHPMailerService::class);

            $body = $this->buildGenericEmail($message);

            return $mailerService->sendGenericEmail($notifiable->email, $notifiable->name, $message->subject, $body);

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
