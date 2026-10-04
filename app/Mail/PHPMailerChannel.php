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
                $mailerService->sendVerificationEmailLater($notifiable, $notification->getCode());

                return true;
            }

            // For password reset emails
            if ($notification instanceof \Illuminate\Auth\Notifications\ResetPassword) {
                $resetUrl = $message->actionUrl;
                $mailerService->sendPasswordResetEmailLater($notifiable, $resetUrl);

                return true;
            }

            // For invitation emails, use the dedicated method
            if ($notification instanceof \App\Notifications\UserInvitation) {
                return $this->sendInvitationEmail($notifiable, $message, $notification);
            }

            // For password reset emails (forgot-password flow), use the
            // dedicated reset template — never the invitation copy.
            if ($notification instanceof \App\Notifications\PasswordResetEmail) {
                return $this->sendPasswordResetEmail($notifiable, $message, $notification);
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

            $mailerService->sendGenericEmailLater(
                $invitation->email,
                $invitation->user->name,
                $message->subject,
                $body
            );

            return true;

        } catch (\Exception $e) {
            \Log::error('Failed to send invitation email: ' . $e->getMessage());
            return false;
        }
    }

    private function sendPasswordResetEmail($notifiable, $message, $notification): bool
    {
        try {
            $mailerService = app(PHPMailerService::class);
            $invitation = $notification->getInvitation();

            $body = $this->buildPasswordResetEmail($message, $invitation);

            $mailerService->sendGenericEmailLater(
                $invitation->email,
                $invitation->user->name,
                $message->subject,
                $body
            );

            return true;

        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
            return false;
        }
    }

    private function buildPasswordResetEmail($message, $invitation): string
    {
        $resetUrl = url('/auth/set-password/' . $invitation->token);
        $expiresAt = $invitation->expires_at->format('F j, Y \a\t g:i A');

        $content = "
            <p>Hello {$invitation->user->name},</p>
            <p>We received a request to reset the password for your ASPIRE account.</p>
            <p>Click the button below to set a new password. This link expires on {$expiresAt}.</p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$resetUrl}' style='display: inline-block; padding: 14px 28px; background: #dc2626; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;'>
                    Reset Password
                </a>
            </div>
            <p>Or copy and paste this link into your browser:</p>
            <p style='word-break: break-all; color: #dc2626;'>{$resetUrl}</p>
            <p>If you did not request a password reset, you can safely ignore this email &mdash; your current password will keep working.</p>
            <p>Best regards,<br>The ASPIRE Team</p>
        ";

        return $this->wrapEmailTemplate($content, $message->subject, '#dc2626');
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
                <p><strong>Invited by:</strong> " . ($invitation->invitedBy?->name ?? 'The Administrator') . "</p>
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

            $mailerService->sendGenericEmailLater($notifiable->email, $notifiable->name, $message->subject, $body);

            return true;

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

    private function wrapEmailTemplate($content, $subject, $headerColor = '#1e40af'): string
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
                .header { background: {$headerColor}; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9fafb; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>ASPIRE System</h1>
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
