<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test {email : Recipient email address}';

    protected $description = 'Send a test email to verify the SMTP mailer is configured correctly';

    public function handle(): int
    {
        $email = $this->argument('email');

        $this->info('Sending test email to ' . $email . ' ...');

        try {
            Mail::raw(
                'This is a test email from Tronmatix. If you can read this, the SMTP mailer is working.',
                fn ($m) => $m->to($email)->subject('Tronmatix SMTP test')
            );

            $this->info('✓ Test email sent. Check the recipient inbox (and spam folder).');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to send test email: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
