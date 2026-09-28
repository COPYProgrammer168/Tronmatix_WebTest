<?php

// app/Rules/GmailVerified.php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GmailVerified implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = strtolower(trim($value));

        // ── Step 1: Must be @gmail.com ────────────────────────────────────────
        if (!str_ends_with($email, '@gmail.com')) {
            $fail('Only Gmail addresses (@gmail.com) are accepted.');
            return;
        }

        // ── Step 2: MX record — confirm Google mail servers respond ───────────
        $domain    = 'gmail.com';
        $mxRecords = [];

        if (!getmxrr($domain, $mxRecords) || empty($mxRecords)) {
            // DNS failure — fail open (don't block the user)
            return;
        }

        // ── Step 3: SMTP probe — RCPT TO handshake ────────────────────────────
        $exists = $this->smtpProbe($email, $mxRecords[0]);

        if ($exists === false) {
            $fail('This Gmail address does not appear to exist. Please use a real Gmail account.');
        }
        // null = inconclusive (fail open) — let it pass
    }

    /**
     * Returns true  = mailbox exists
     *         false = mailbox does not exist (550/551 response)
     *         null  = inconclusive (connection blocked, timeout, etc.) — fail open
     */
    private function smtpProbe(string $email, string $mxHost): ?bool
    {
        try {
            $socket = @fsockopen($mxHost, 25, $errno, $errstr, 5);

            if (!$socket) {
                return null; // Firewall / port blocked — fail open
            }

            stream_set_timeout($socket, 5);

            // Read banner
            $banner = $this->readLine($socket);
            if (!str_starts_with($banner, '220')) {
                fclose($socket);
                return null;
            }

            // EHLO
            fwrite($socket, "EHLO tronmatix.com\r\n");
            $this->drainResponse($socket);

            // MAIL FROM
            fwrite($socket, "MAIL FROM:<noreply@tronmatix.com>\r\n");
            $resp = $this->readLine($socket);
            if (!str_starts_with($resp, '250')) {
                fclose($socket);
                return null;
            }

            // RCPT TO — the actual existence check
            fwrite($socket, "RCPT TO:<{$email}>\r\n");
            $resp = $this->readLine($socket);

            fwrite($socket, "QUIT\r\n");
            fclose($socket);

            // 250 / 251 = accepted (exists)
            // 550 / 551 / 552 / 553 = rejected (does not exist)
            if (str_starts_with($resp, '250') || str_starts_with($resp, '251')) {
                return true;
            }
            if (str_starts_with($resp, '55') || str_starts_with($resp, '45')) {
                return false;
            }

            return null; // Unknown response — fail open

        } catch (\Throwable $e) {
            return null; // Never block the user due to our own error
        }
    }

    /** Read one complete SMTP response (handles multi-line 250-... 250 OK) */
    private function readLine($socket): string
    {
        $response = '';
        while (!feof($socket)) {
            $line = fgets($socket, 512);
            if ($line === false) break;
            $response = $line;
            // 4th char is ' ' = last line of response
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $response;
    }

    /** Drain a full multi-line SMTP response (e.g. EHLO capabilities) */
    private function drainResponse($socket): void
    {
        while (!feof($socket)) {
            $line = fgets($socket, 512);
            if ($line === false) break;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
    }
}
