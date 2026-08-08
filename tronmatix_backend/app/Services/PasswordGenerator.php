<?php

namespace App\Services;

use Illuminate\Support\Str;

class PasswordGenerator
{
    /**
     * Generate a secure random password.
     */
    public static function generate(int $length = 12): string
    {
        return Str::random($length);
    }
}
