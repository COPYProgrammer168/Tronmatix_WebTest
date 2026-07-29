<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'actor_id',
        'actor_type',
        'actor_name',
        'action',
        'entity_type',
        'entity_id',
        'entity_name',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public static function log(array $data): self
    {
        return static::create($data);
    }
}