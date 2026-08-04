<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'organization', 'location', 'started_at', 'ended_at',
        'is_current', 'description', 'achievements', 'sort_order', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at' => 'date',
            'is_current' => 'boolean',
            'achievements' => 'array',
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getPeriodLabelAttribute(): string
    {
        $start = $this->started_at?->format('Y') ?? '—';
        $end = $this->is_current ? 'Present' : ($this->ended_at?->format('Y') ?? '—');

        return "{$start} — {$end}";
    }
}
