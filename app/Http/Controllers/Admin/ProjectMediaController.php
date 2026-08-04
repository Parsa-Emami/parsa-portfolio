<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectMediaRequest;
use App\Models\Project;
use App\Models\ProjectMedia;
use App\Services\ImageOptimizerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectMediaController extends Controller
{
    public function __construct(private readonly ImageOptimizerService $images) {}

    public function store(ProjectMediaRequest $request, Project $project): RedirectResponse
    {
        $nextOrder = (int) $project->media()->max('sort_order') + 1;

        foreach ($request->file('images', []) as $file) {
            $stored = $this->images->store($file, "projects/{$project->id}/gallery");
            $project->media()->create([
                'type' => 'image',
                'path' => $stored['path'],
                'thumbnail_path' => $stored['thumbnail_path'],
                'width' => $stored['width'],
                'height' => $stored['height'],
                'alt_text' => $request->string('alt_text')->toString() ?: $project->title,
                'caption' => $request->string('caption')->toString() ?: null,
                'display_size' => $request->string('display_size')->toString(),
                'sort_order' => $nextOrder++,
            ]);
        }

        if ($request->filled('external_url')) {
            $project->media()->create([
                'type' => 'video',
                'external_url' => $this->normalizeVideoUrl($request->string('external_url')->toString()),
                'alt_text' => $request->string('alt_text')->toString() ?: $project->title.' video',
                'caption' => $request->string('caption')->toString() ?: null,
                'display_size' => $request->string('display_size')->toString(),
                'sort_order' => $nextOrder,
            ]);
        }

        return back()->with('success', 'رسانه‌های پروژه اضافه شدند.');
    }

    public function update(Request $request, Project $project, ProjectMedia $media): RedirectResponse
    {
        abort_unless($media->project_id === $project->id, 404);
        $data = $request->validate([
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'display_size' => ['required', 'in:standard,wide,portrait'],
            'is_featured' => ['nullable', 'boolean'],
        ]);
        $data['is_featured'] = $request->boolean('is_featured');

        if ($data['is_featured']) {
            $project->media()->where($media->getKeyName(), '!=', $media->getKey())->update(['is_featured' => false]);
        }

        $media->update($data);

        return back()->with('success', 'اطلاعات رسانه ذخیره شد.');
    }

    public function destroy(Project $project, ProjectMedia $media): RedirectResponse
    {
        abort_unless($media->project_id === $project->id, 404);
        $this->images->delete($media->path, $media->thumbnail_path);
        $media->delete();

        return back()->with('success', 'رسانه حذف شد.');
    }

    private function normalizeVideoUrl(string $url): string
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true) && $path !== '') {
            return 'https://www.youtube.com/embed/'.rawurlencode(explode('/', $path)[0]);
        }

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            parse_str($parts['query'] ?? '', $query);
            $videoId = $query['v'] ?? null;

            if ($videoId) {
                return 'https://www.youtube-nocookie.com/embed/'.rawurlencode((string) $videoId);
            }

            if (str_starts_with($path, 'embed/')) {
                return 'https://www.youtube-nocookie.com/'.$path;
            }
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com'], true) && ctype_digit($path)) {
            return 'https://player.vimeo.com/video/'.$path;
        }

        return $url;
    }

    public function reorder(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate(['items' => ['required', 'array'], 'items.*' => ['integer']]);
        $allowed = $project->media()->whereIn('id', $validated['items'])->pluck('id')->all();

        foreach (array_values($validated['items']) as $order => $id) {
            if (in_array($id, $allowed, true)) {
                ProjectMedia::query()->whereKey($id)->update(['sort_order' => $order]);
            }
        }

        return response()->json(['success' => true]);
    }
}
