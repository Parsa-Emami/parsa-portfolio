<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Services\ImageOptimizerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(private readonly ImageOptimizerService $images) {}

    public function index(): View
    {
        return view('admin.projects.index', [
            'projects' => Project::query()->withCount('media')->orderBy('sort_order')->orderByDesc('created_at')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.projects.create', [
            'project' => new Project([
                'accent' => '#d7ff3f', 'sort_order' => 0, 'year' => now()->year, 'is_published' => true,
            ]),
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $project = Project::query()->create($this->normalizedData($request));

        return redirect()->route('admin.projects.edit', $project)->with('success', 'پروژه ساخته شد؛ حالا گالری آن را تکمیل کن.');
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.edit', ['project' => $project->load('media')]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($this->normalizedData($request, $project));

        return back()->with('success', 'تغییرات پروژه ذخیره شد.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        foreach ($project->media as $media) {
            $this->images->delete($media->path, $media->thumbnail_path);
            $media->delete();
        }
        $this->images->delete($project->cover_image, $project->og_image);
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'پروژه حذف شد.');
    }

    private function normalizedData(ProjectRequest $request, ?Project $project = null): array
    {
        $data = $request->validated();
        $removeCover = (bool) ($data['remove_cover_image'] ?? false);
        $removeOg = (bool) ($data['remove_og_image'] ?? false);
        unset($data['remove_cover_image'], $data['remove_og_image']);

        if ($request->hasFile('cover_image')) {
            $this->images->delete($project?->cover_image);
            $data['cover_image'] = $this->images->store($request->file('cover_image'), 'projects/covers')['path'];
        } elseif ($removeCover) {
            $this->images->delete($project?->cover_image);
            $data['cover_image'] = null;
        } else {
            unset($data['cover_image']);
        }

        if ($request->hasFile('og_image')) {
            $this->images->delete($project?->og_image);
            $data['og_image'] = $this->images->store($request->file('og_image'), 'projects/og', 1600)['path'];
        } elseif ($removeOg) {
            $this->images->delete($project?->og_image);
            $data['og_image'] = null;
        } else {
            unset($data['og_image']);
        }

        if (($data['is_published'] ?? false) && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
