<?php

// app/Services/SmsSender.php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

/**
 * SMS delivery for the dashboard phone-OTP password reset.
 *
 * Gated by SMS_DRIVER in .env:
 *   - 'twilio' → real SMS via the Twilio REST API (requires credentials)
 *   - 'log'    → writes the message to the security log (dev/testing without
 *                a live Twilio account, so the OTP flow can be verified)
 */
class SmsSender
{
    public function send(string $phone, string $message): bool
    {
        $driver = config('services.sms.driver', 'log');

        if ($driver === 'twilio') {
            return $this->sendViaTwilio($phone, $message);
        }

        // Default log driver — the OTP appears in the security log for dev/testing.
        // Uses notice() so it isn't filtered out by the security channel's
        // default 'notice' level threshold.
        Log::channel('security')->notice('SMS [log-driver] to ' . $phone . ': ' . $message);

        return true;
    }

    private function sendViaTwilio(string $phone, string $message): bool
    {
        $sid   = config('services.sms.twilio_sid');
        $token = config('services.sms.twilio_token');
        $from  = config('services.sms.twilio_from');

        if (! $sid || ! $token || ! $from) {
            Log::channel('security')->warning('Twilio is not configured. Set TWILIO_SID, TWILIO_TOKEN, TWILIO_FROM.');

            return false;
        }

        try {
            $twilio = new Client($sid, $token);
            $twilio->messages->create($phone, [
                'from' => $from,
                'body' => $message,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::channel('security')->warning('Twilio SMS failed: ' . $e->getMessage());

            return false;
        }
    }
}
