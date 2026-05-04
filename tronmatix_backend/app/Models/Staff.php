<?php

// app/Models/Staff.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Staff extends Authenticatable
{
    use Notifiable;

    protected $guard = 'staff';

    protected $table = 'staff';

    const ROLES = ['editor', 'seller', 'delivery', 'developer'];

    protected $fillable = [
        'name', 'email', 'username', 'password',
        'avatar', 'role', 'is_active', 'last_login_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return (bool) $this->is_active;
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
