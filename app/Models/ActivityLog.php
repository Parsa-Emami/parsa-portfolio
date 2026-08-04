<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use MassPrunable;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'request_id',
        'route_name',
        'method',
        'path',
        'action',
        'status_code',
        'ip_hash',
        'user_agent_hash',
        'payload_keys',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload_keys' => 'array',
            'status_code' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prunable(): Builder
    {
        $days = max(30, (int) config('production.monitoring.activity_retention_days', 180));

        return static::query()->where('created_at', '<', now()->subDays($days));
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }
}
