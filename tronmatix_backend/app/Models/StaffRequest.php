<?php

// app/Models/StaffRequest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffRequest extends Model
{
    protected $fillable = [
        'name', 'email', 'username', 'password',
        'requested_role', 'message', 'status',
        'reviewed_by', 'reviewed_at',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
