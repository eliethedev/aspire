<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class PHPMailerService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    private function shouldSend(): bool
    {
        if (!config('phpmailer.enabled', true)) {
            return false;
        }

        return !app()->environment('testing')
            && !in_array(config('mail.default'), ['array', 'log'], true);
    }

    private function configure(): void
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = config('phpmailer.host', 'smtp.gmail.com');
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = config('phpmailer.username');
            $this->mailer->Password = config('phpmailer.password');
            
            // Set encryption based on configuration
            $encryption = config('phpmailer.encryption', 'tls');
            if ($encryption === 'tls') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($encryption === 'ssl') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }
            
            $this->mailer->Port = config('phpmailer.port', 587);

            // Bound how long a single SMTP operation may block the request.
            // PHPMailer defaults to 300 seconds, which can exceed PHP's
            // max_execution_time and make form submissions appear to hang.
            $this->mailer->Timeout = max(5, (int) config('phpmailer.timeout', 15));

            // Cap the reply-wait (`stream_select`) so a dead server raises a
            // catchable PHPMailer exception instead of PHP's max_execution_time
            // fatal error (SMTP::get_lines()).
            $this->mailer->SMTP = new \PHPMailer\PHPMailer\SMTP();
            $this->mailer->SMTP->setTimeout(max(5, (int) config('phpmailer.timeout', 15)));
            $this->mailer->SMTP->Timelimit = max(5, (int) config('phpmailer.timeout', 15));

            // Recipients
            $this->mailer->setFrom(
                config('phpmailer.from.address', 'noreply@aspire.edu'),
                config('phpmailer.from.name', 'ASPIRE System')
            );

            // Content settings
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';

            // Debug settings
            if (config('phpmailer.debug', false)) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
                $this->mailer->Debugoutput = function($str, $level) {
                    Log::debug("PHPMailer [{$level}]: {$str}");
                };
            }

            // SMTP options
            $smtpOptions = config('phpmailer.smtp_options', []);
            if (config('phpmailer.force_tls_1_2', false)) {
                $smtpOptions['ssl']['crypto_method'] = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            }
            if (!empty($smtpOptions)) {
                $this->mailer->SMTPOptions = $smtpOptions;
            }

        } catch (Exception $e) {
            Log::error('PHPMailer configuration failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function sendVerificationEmail($user, $verificationUrl): bool
    {
        if (!$this->shouldSend()) {
            return true;
        }

        try {
            $this->mailer->addAddress($user->email, $user->name);
            $this->mailer->Subject = 'ASPIRE - Verify Your Email';
            
            $this->mailer->Body = $this->getVerificationEmailTemplate($user, $verificationUrl);
            $this->mailer->AltBody = strip_tags($this->mailer->Body);

            return $this->mailer->send();

        } catch (Exception $e) {
            Log::error('Failed to send verification email: ' . $e->getMessage());
            return false;
        }
    }

    public function sendPasswordResetEmail($user, $resetUrl): bool
    {
        if (!$this->shouldSend()) {
            return true;
        }

        try {
            $this->mailer->addAddress($user->email, $user->name);
            $this->mailer->Subject = 'Reset Your ASPIRE Password';

            $this->mailer->Body = $this->getPasswordResetEmailTemplate($user, $resetUrl);
            $this->mailer->AltBody = strip_tags($this->mailer->Body);

            return $this->mailer->send();

        } catch (Exception $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage());
            return false;
        }
    }

    public function sendInvitationEmail($invitation, $subject, $body): bool
    {
        if (!$this->shouldSend()) {
            return true;
        }

        try {
            $this->mailer->addAddress($invitation->email, $invitation->user->name);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            return $this->mailer->send();

        } catch (Exception $e) {
            Log::error('Failed to send invitation email: ' . $e->getMessage());
            return false;
        }
    }

    public function sendGenericEmail($email, $name, $subject, $body): bool
    {
        if (!$this->shouldSend()) {
            return true;
        }

        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            return $this->mailer->send();

        } catch (Exception $e) {
            Log::error('Failed to send generic email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hand a generic email off to a detached background process so slow or
     * unreachable SMTP servers never delay the user's HTTP request.
     * If spawning fails, the email is skipped (non-critical).
     */
    public function sendGenericEmailLater($email, $name, $subject, $body): void
    {
        $this->spawnDeferred($email, $name, $subject, $body);
    }

    /**
     * Deferred variant of sendVerificationEmail.
     */
    public function sendVerificationEmailLater($user, $verificationUrl): void
    {
        $subject = 'ASPIRE - Verify Your Email';
        $body = $this->getVerificationEmailTemplate($user, $verificationUrl);
        $this->spawnDeferred($user->email, $user->name, $subject, $body);
    }

    /**
     * Deferred variant of sendPasswordResetEmail.
     */
    public function sendPasswordResetEmailLater($user, $resetUrl): void
    {
        $subject = 'Reset Your ASPIRE Password';
        $body = $this->getPasswordResetEmailTemplate($user, $resetUrl);
        $this->spawnDeferred($user->email, $user->name, $subject, $body);
    }

    /**
     * Write the message body to a temp file and hand it to a detached
     * `aspire:send-deferred-email` process so the HTTP request never blocks
     * on a slow or unreachable SMTP server.
     */
    private function spawnDeferred($email, $name, $subject, $body): void
    {
        try {
            $bodyPath = tempnam(sys_get_temp_dir(), 'aspire_mail_');
            file_put_contents($bodyPath, $body);

            // Some SAPI contexts (e.g. `php artisan serve`) expose a stripped
            // $_SERVER, which Symfony Process would otherwise use as the child
            // environment. Without SystemRoot etc., Windows networking (DNS)
            // cannot initialize in the child, so pass the essentials explicitly.
            $env = [];
            foreach (['SystemRoot', 'windir', 'SystemDrive', 'COMSPEC', 'PATHEXT', 'PATH', 'TEMP', 'TMP'] as $key) {
                $value = getenv($key);
                if ($value !== false && $value !== '') {
                    $env[$key] = $value;
                }
            }

            $process = new \Symfony\Component\Process\Process(array_filter([
                PHP_BINARY,
                defined('ARTISAN_BINARY') ? ARTISAN_BINARY : base_path('artisan'),
                'aspire:send-deferred-email',
                '--to='.((string) $email),
                '--name='.((string) $name),
                '--subject='.((string) $subject),
                '--body-path='.$bodyPath,
            ]), base_path(), $env);
            $process->setOptions(['create_new_console' => true]);
            $process->start();

            return;
        } catch (\Throwable $e) {
            Log::error('Could not spawn deferred email process, skipping email: '.$e->getMessage());
        }
    }

    private function getVerificationEmailTemplate($user, $verificationUrl): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Email Verification</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #1e40af; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9fafb; }
                .button { display: inline-block; padding: 12px 24px; background: #1e40af; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
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
                    <h2>Welcome to ASPIRE, {$user->name}!</h2>
                    <p>Thank you for registering with the ASPIRE system for DepEd Philippines.</p>
                    <p>Please click the button below to verify your email address and activate your account:</p>
                    <div style='text-align: center;'>
                        <a href='{$verificationUrl}' class='button text-white'>Verify Email Address</a>
                    </div>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; color: #1e40af;'>{$verificationUrl}</p>
                    <p><strong>Note:</strong> This verification link will expire in 24 hours.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from the ASPIRE system. Please do not reply to this email.</p>
                    <p>© 2026 ASPIRE - Department of Education Sagay City, Negros Occidental, Philippines</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function getPasswordResetEmailTemplate($user, $resetUrl): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Password Reset</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc2626; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9fafb; }
                .button { display: inline-block; padding: 12px 24px; background: #dc2626; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>ASPIRE System</h1>
                    <p>Password Reset Request</p>
                </div>
                <div class='content'>
                    <h2>Password Reset Request</h2>
                    <p>Hello {$user->name},</p>
                    <p>We received a request to reset your password for your ASPIRE account.</p>
                    <p>Click the button below to reset your password:</p>
                    <div style='text-align: center;'>
                        <a href='{$resetUrl}' class='button text-white'>Reset Password</a>
                    </div>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; color: #dc2626;'>{$resetUrl}</p>
                    <p><strong>Note:</strong> This password reset link will expire in 1 hour.</p>
                    <p>If you didn't request this password reset, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from the ASPIRE system. Please do not reply to this email.</p>
                    <p>© 2026 ASPIRE - Department of Education Sagay City, Negros Occidental, Philippines</p>
                </div>
            </div>
        </body>
        </html>";
    }

    public function testConnection(): bool
    {
        if (!$this->shouldSend()) {
            return true;
        }

        try {
            return $this->mailer->smtpConnect();
        } catch (Exception $e) {
            Log::error('SMTP connection test failed: ' . $e->getMessage());
            return false;
        }
    }
}
