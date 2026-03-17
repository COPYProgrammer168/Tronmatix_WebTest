<?php

// app/Models/Admin.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $guard = 'admin';

    const ROLES = ['admin', 'superadmin'];

    protected $fillable = [
        'name', 'email', 'username', 'password',
        'avatar', 'role', 'is_active', 'last_login_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    // FIX [2]: isActive() — middleware checks $admin->isActive()
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    // FIX [1]: recordLogin() — DashboardAuthController calls this on successful login
    public function recordLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    public function getAvatarInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name ?? ''));

        return strtoupper(
            count($words) >= 2
                ? ($words[0][0] ?? '').($words[1][0] ?? '')
                : substr($this->name ?? 'AD', 0, 2)
        );
    }
}
