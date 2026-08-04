<?php

namespace App\Services;

use App\Models\Experience;
use App\Models\Project;
use App\Models\ProjectMedia;
use App\Models\SiteSetting;
use App\Models\Skill;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PortfolioContentSnapshot
{
    public const SCHEMA_VERSION = 1;

    public function __construct(private readonly Filesystem $files)
    {
    }

    /**
     * Export only public portfolio content. Users, contact messages, activity logs,
     * sessions and credentials are intentionally excluded.
     */
    public function export(string $jsonPath, string $mediaPath): array
    {
        $this->files->ensureDirectoryExists(dirname($jsonPath));
        $this->files->deleteDirectory($mediaPath);
        $this->files->ensureDirectoryExists($mediaPath);

        $settings = SiteSetting::query()
            ->orderBy('sort_order')
            ->orderBy('key')
            ->get()
            ->map(fn (SiteSetting $setting): array => Arr::only($setting->toArray(), [
                'key', 'label', 'value', 'group', 'type', 'sort_order',
            ]))
            ->values()
            ->all();

        $projects = Project::query()
            ->published()
            ->with('media')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Project $project) use ($mediaPath): array {
                $data = Arr::only($project->toArray(), [
                    'title', 'slug', 'eyebrow', 'summary', 'content', 'challenge',
                    'solution', 'architecture', 'results', 'role', 'client', 'year',
                    'technologies', 'cover_image', 'cover_alt', 'accent', 'github_url',
                    'live_url', 'video_url', 'seo_title', 'seo_description', 'og_image',
                    'is_featured', 'is_published', 'sort_order',
                ]);

                $data['published_at'] = $project->published_at?->toAtomString();
                $data['media'] = $project->media
                    ->map(function (ProjectMedia $media) use ($mediaPath): array {
                        $this->copyPublicFile($media->path, $mediaPath);
                        $this->copyPublicFile($media->thumbnail_path, $mediaPath);

                        return Arr::only($media->toArray(), [
                            'type', 'path', 'thumbnail_path', 'external_url', 'alt_text',
                            'caption', 'display_size', 'width', 'height', 'sort_order',
                            'is_featured',
                        ]);
                    })
                    ->values()
                    ->all();

                $this->copyPublicFile($project->cover_image, $mediaPath);
                $this->copyPublicFile($project->og_image, $mediaPath);

                return $data;
            })
            ->values()
            ->all();

        foreach ($settings as $setting) {
            if (in_array($setting['key'], ['resume_file', 'site_og_image'], true)) {
                $this->copyPublicFile($setting['value'] ?? null, $mediaPath);
            }
        }

        $skills = Skill::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Skill $skill): array => Arr::only($skill->toArray(), [
                'name', 'category', 'proficiency', 'short_label', 'sort_order', 'is_published',
            ]))
            ->values()
            ->all();

        $experiences = Experience::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Experience $experience): array {
                $data = Arr::only($experience->toArray(), [
                    'title', 'organization', 'location', 'is_current', 'description',
                    'achievements', 'sort_order', 'is_published',
                ]);
                $data['started_at'] = $experience->started_at?->format('Y-m-d');
                $data['ended_at'] = $experience->ended_at?->format('Y-m-d');

                return $data;
            })
            ->values()
            ->all();

        $snapshot = [
            'schema_version' => self::SCHEMA_VERSION,
            'generated_at' => now()->toAtomString(),
            'settings' => $settings,
            'projects' => $projects,
            'skills' => $skills,
            'experiences' => $experiences,
        ];

        $json = json_encode(
            $snapshot,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        $this->files->put($jsonPath, $json."\n");

        return [
            'json_path' => $jsonPath,
            'media_path' => $mediaPath,
            'projects' => count($projects),
            'skills' => count($skills),
            'experiences' => count($experiences),
            'settings' => count($settings),
        ];
    }

    public function import(string $jsonPath, string $mediaPath, bool $replace = false): array
    {
        if (! $this->files->exists($jsonPath)) {
            throw new RuntimeException('Portfolio content snapshot not found: '.$jsonPath);
        }

        $snapshot = json_decode($this->files->get($jsonPath), true, flags: JSON_THROW_ON_ERROR);

        if (($snapshot['schema_version'] ?? null) !== self::SCHEMA_VERSION) {
            throw new RuntimeException('Unsupported portfolio content snapshot schema version.');
        }

        $this->restoreMedia($mediaPath);

        DB::transaction(function () use ($snapshot, $replace): void {
            if ($replace) {
                ProjectMedia::query()->delete();
                Project::withTrashed()->forceDelete();
                Skill::query()->delete();
                Experience::query()->delete();
                SiteSetting::query()->delete();
            }

            foreach ($snapshot['settings'] ?? [] as $setting) {
                SiteSetting::query()->updateOrCreate(
                    ['key' => $setting['key']],
                    Arr::only($setting, ['label', 'value', 'group', 'type', 'sort_order'])
                );
            }

            foreach ($snapshot['projects'] ?? [] as $projectData) {
                $media = $projectData['media'] ?? [];
                unset($projectData['media']);

                $project = Project::withTrashed()->updateOrCreate(
                    ['slug' => $projectData['slug']],
                    Arr::only($projectData, (new Project())->getFillable())
                );

                $project->restore();
                $project->media()->delete();

                foreach ($media as $mediaData) {
                    $project->media()->create(Arr::only($mediaData, (new ProjectMedia())->getFillable()));
                }
            }

            foreach ($snapshot['skills'] ?? [] as $skillData) {
                Skill::query()->updateOrCreate(
                    ['name' => $skillData['name'], 'category' => $skillData['category']],
                    Arr::only($skillData, (new Skill())->getFillable())
                );
            }

            foreach ($snapshot['experiences'] ?? [] as $experienceData) {
                Experience::query()->updateOrCreate(
                    [
                        'title' => $experienceData['title'],
                        'organization' => $experienceData['organization'] ?? null,
                        'started_at' => $experienceData['started_at'] ?? null,
                    ],
                    Arr::only($experienceData, (new Experience())->getFillable())
                );
            }
        });

        SiteSetting::forgetCache();

        return [
            'projects' => count($snapshot['projects'] ?? []),
            'skills' => count($snapshot['skills'] ?? []),
            'experiences' => count($snapshot['experiences'] ?? []),
            'settings' => count($snapshot['settings'] ?? []),
        ];
    }

    private function copyPublicFile(?string $path, string $mediaPath): void
    {
        $relative = $this->normalizeRelativePath($path);

        if ($relative === null || ! Storage::disk('public')->exists($relative)) {
            return;
        }

        $destination = $mediaPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $this->files->ensureDirectoryExists(dirname($destination));
        $this->files->copy(Storage::disk('public')->path($relative), $destination);
    }

    private function restoreMedia(string $mediaPath): void
    {
        if (! is_dir($mediaPath)) {
            return;
        }

        $destination = rtrim(Storage::disk('public')->path(''), DIRECTORY_SEPARATOR);
        $this->files->ensureDirectoryExists($destination);
        $this->files->copyDirectory($mediaPath, $destination);
    }

    private function normalizeRelativePath(?string $path): ?string
    {
        if (blank($path) || str_starts_with((string) $path, 'http://') || str_starts_with((string) $path, 'https://')) {
            return null;
        }

        $relative = ltrim(str_replace('\\', '/', (string) $path), '/');

        if ($relative === '' || str_contains($relative, '../') || $relative === '..') {
            return null;
        }

        return $relative;
    }
}
