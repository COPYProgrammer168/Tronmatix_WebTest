<?php

namespace App\Support;

/**
 * Normalizes phone numbers to a canonical form so lookups work regardless of
 * whether a phone is stored/entered in local Cambodian format ("067 114 814"),
 * international E.164 ("+85567114814"), or with the "00" prefix.
 *
 * The canonical form is digits-only with the country code prepended:
 *   "067 114 814"  → "85567114814"
 *   "+85567114814" → "85567114814"
 *   "85567114814"  → "85567114814"
 */
class PhoneHelper
{
    private static function countryCode(): string
    {
        return ltrim((string) config('services.sms.country_code', '855'), '+');
    }

    /**
     * Canonical digits-only form with country code, or null if not a valid
     * local/international number for the configured country.
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        // International dialing prefix "00<cc><number>"
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        $cc = self::countryCode();

        // Already has the country code (e.g. "85567114814" / "+855...") — accept
        // 11 or 12 digits (Cambodian local numbers are 8 or 9 digits).
        if (str_starts_with($digits, $cc) && strlen($digits) >= strlen($cc) + 8 && strlen($digits) <= strlen($cc) + 9) {
            return $digits;
        }

        // Local trunk "0..." → drop the leading zero.
        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        $canonical = $cc . $digits;

        // Valid Cambodian number: 8 or 9 local digits → 11 or 12 with country code.
        return (strlen($canonical) === strlen($cc) + 8 || strlen($canonical) === strlen($cc) + 9)
            ? $canonical
            : null;
    }

    /** E.164 form ("+85567114814"), or null if invalid. */
    public static function toE164(?string $phone): ?string
    {
        $n = self::normalize($phone);

        return $n === null ? null : '+' . $n;
    }
}
