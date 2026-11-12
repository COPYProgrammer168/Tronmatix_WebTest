<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffInviteLink extends Model
{
    protected $table = 'staff_invite_links';

    protected $fillable = [
        'staff_invite_id',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public function invite()
    {
        return $this->belongsTo(StaffInvite::class, 'staff_invite_id');
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->isUsed() && !$this->isExpired();
    }

    public function use(): void
    {
        $this->update(['used_at' => now()]);
    }

    public function inviteUrl(): string
    {
        return url('/dashboard/invite/' . $this->token);
    }
}
