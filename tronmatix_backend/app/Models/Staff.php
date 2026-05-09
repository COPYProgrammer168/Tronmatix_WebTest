<?php

// app/Models/Staff.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;              // ADD — required for Sanctum token

class Staff extends Authenticatable
{
    use HasApiTokens, Notifiable;              // ADD HasApiTokens

    protected $guard = 'staff';

    protected $table = 'staff';

    const ROLES = ['editor', 'seller', 'delivery', 'developer'];

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'avatar',
        'role',
        'is_active',
        'last_login_at',
        'last_seen_at',
        'online_status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'online_status' => 'string',
    ];

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function getIsOnlineAttribute(): bool
    {
        return $this->isOnline();
    }
    
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function isOnline(): bool
    {
        return $this->online_status === 'online'
            && $this->last_seen_at
            && $this->last_seen_at->greaterThan(now()->subMinutes(2));
    }

    public function canAccessStaffPortal(): bool   // ADD: used by StaffAuthController
    {
        return in_array($this->role, ['editor', 'seller', 'delivery'], true);
    }

    public function canAccessDevPortal(): bool     // ADD: used by DevAuthController
    {
        return $this->role === 'developer';
    }

    public function recordLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    public function getAvatarInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name ?? ''));

        return strtoupper(
            count($words) >= 2
            ? ($words[0][0] ?? '') . ($words[1][0] ?? '')
            : substr($this->name ?? 'ST', 0, 2)
        );
    }
}
