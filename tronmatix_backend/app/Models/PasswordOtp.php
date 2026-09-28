<?php

// app/Models/PasswordOtp.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A short-lived single-use OTP issued for phone-based password reset on the
 * dashboard (admin/staff). Mirrors the TelegramConnectionToken pattern.
 */
class PasswordOtp extends Model
{
    protected $table = 'password_otps';

    protected $fillable = [
        'phone',
        'mode',
        'otp',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /** Valid (unused, unexpired) OTP? */
    public function isValid(): bool
    {
        return ! $this->isUsed() && ! $this->isExpired();
    }

    /** Mark the OTP as consumed so it can never be replayed. */
    public function use(): void
    {
        $this->update(['used_at' => now()]);
    }
}
