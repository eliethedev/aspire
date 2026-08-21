<?php

namespace App\Console\Commands;

use App\Services\PHPMailerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDeferredEmail extends Command
{
    protected $signature = 'aspire:send-deferred-email
                            {--to= : Recipient email address}
                            {--name= : Recipient display name}
                            {--subject= : Email subject}
                            {--body-path= : Absolute path to a file containing the HTML body}';

    protected $description = 'Send a generic email outside the requesting HTTP process (fire-and-forget)';

    public function handle(PHPMailerService $mailer): int
    {
        $to = (string) $this->option('to');
        $name = (string) $this->option('name');
        $subject = (string) $this->option('subject');
        $bodyPath = (string) $this->option('body-path');

        if ($to === '' || $bodyPath === '') {
            $this->error('Missing --to or --body-path.');

            return self::FAILURE;
        }

        $body = @file_get_contents($bodyPath);
        @unlink($bodyPath);

        if ($body === false || $body === '') {
            Log::error('Deferred email aborted: body file missing or empty.', ['path' => $bodyPath]);

            return self::FAILURE;
        }

        $sent = $mailer->sendGenericEmail($to, $name !== '' ? $name : $to, $subject, $body);

        if (! $sent) {
            Log::error('Deferred email failed to send.', ['to' => $to, 'subject' => $subject]);

            return self::FAILURE;
        }

        Log::info('Deferred email sent.', ['to' => $to, 'subject' => $subject]);

        return self::SUCCESS;
    }
}
