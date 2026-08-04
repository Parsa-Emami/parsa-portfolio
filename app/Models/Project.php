<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'eyebrow', 'summary', 'content', 'challenge', 'solution',
        'architecture', 'results', 'role', 'client', 'year', 'technologies',
        'cover_image', 'cover_alt', 'accent', 'github_url', 'live_url', 'video_url',
        'seo_title', 'seo_description', 'og_image', 'is_featured', 'is_published',
        'sort_order', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
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
                ->when($project->exists, fn (Builder $query) => $query->where($project->getKeyName(), '!=', $project->getKey()))
                ->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $project->slug = $slug;
        });
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProjectMedia::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(fn (Builder $query) => $query
                ->whereNull('published_at')
                ->orWhere('published_at', '<=', now()));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSeoTitleAttribute($value): string
    {
        return $value ?: $this->title;
    }

    public function getSeoDescriptionAttribute($value): string
    {
        return $value ?: Str::limit(strip_tags($this->summary), 155, '');
    }
}
