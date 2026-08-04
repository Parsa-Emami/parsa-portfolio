<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'eyebrow',
        'summary',
        'content',
        'role',
        'year',
        'technologies',
        'cover_image',
        'accent',
        'github_url',
        'live_url',
        'is_featured',
        'is_published',
        'sort_order',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Project $project): void {
            $baseSlug = Str::slug($project->slug ?: $project->title) ?: 'project';
            $slug = $baseSlug;
            $counter = 2;

            while (static::withTrashed()
                ->where('slug', $slug)
                ->when(
                    $project->exists,
                    fn (Builder $query) => $query->where(
                        $project->getKeyName(),
                        '!=',
                        $project->getKey()
                    )
                )
                ->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $project->slug = $slug;
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
