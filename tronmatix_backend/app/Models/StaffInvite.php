<?php

// app/Models/StaffInvite.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A pending staff invitation. The admin creates one from the Staff page,
 * copies the generated link and shares it (e.g. via Telegram/chat).
 * The invited person opens the link, sets their own password, and only
 * then is a real Staff account created.
 */
class StaffInvite extends Model
{
    protected $table = 'staff_invites';

    protected $fillable = [
        'token',
        'name',
        'username',
        'email',
        'phone',
        'role',
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

    /** Valid, reusable link? */
    public function isValid(): bool
    {
        return ! $this->isUsed() && ! $this->isExpired();
    }

    /** Mark the invite as redeemed. */
    public function use(): void
    {
        $this->update(['used_at' => now()]);
    }

    public function inviteUrl(): string
    {
        return url('/dashboard/invite/' . $this->token);
    }

    public function links()
    {
        return $this->hasMany(StaffInviteLink::class, 'staff_invite_id');
    }

    public function activeLinks()
    {
        return $this->links()->whereNull('used_at')->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
