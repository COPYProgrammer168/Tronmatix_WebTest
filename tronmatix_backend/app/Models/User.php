<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method \Laravel\Sanctum\PersonalAccessToken|null currentAccessToken()
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    const ROLES = ['customer', 'vip', 'reseller', 'banned'];

    const ROLE_LABELS = [
        'customer' => 'Customer',
        'vip'      => 'VIP',
        'reseller' => 'Reseller',
        'banned'   => 'Banned',
    ];

    protected $fillable = [
        'username', 'name', 'email', 'password',
        'phone', 'avatar',
        'role', 'is_banned',
        'two_factor_secret', 'two_factor_enabled', 'two_factor_confirmed_at',
        // FIX: Telegram fields must be fillable for connectUser() / disconnectUser()
        'telegram_chat_id',
        'telegram_username',
        'telegram_connected_at',
        'google_id',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at'       => 'datetime',
        'two_factor_enabled'      => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
        'is_banned'               => 'boolean',
        'telegram_connected_at'   => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(UserLocation::class);
    }

    public function defaultLocation(): HasOne
    {
        return $this->hasOne(UserLocation::class)->where('is_default', true);
    }

    public function latestOrder(): HasOne
    {
        return $this->hasOne(Order::class)->latestOfMany();
    }

    public function chatSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getAvatarInitialsAttribute(): string
    {
        return strtoupper(substr($this->username ?? $this->name ?? '?', 0, 2));
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLE_LABELS[$this->role ?? 'customer'] ?? 'Customer';
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isVip(): bool      { return $this->role === 'vip'; }
    public function isReseller(): bool { return $this->role === 'reseller'; }
    public function isBanned(): bool   { return $this->role === 'banned' || (bool) $this->is_banned; }
    public function isAdmin(): bool    { return $this->role === 'admin'; }

    public function totalSpent(): float
    {
        return (float) $this->orders()
            ->whereNotIn('status', [Order::STATUS_CANCELLED])
            ->sum('total');
    }
}
