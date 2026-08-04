<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('admin.projects.index', [
            'projects' => Project::query()
                ->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.projects.create', [
            'project' => new Project([
                'accent' => '#d7ff3f',
                'sort_order' => 0,
                'year' => now()->year,
                'is_published' => true,
            ]),
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $data = $this->normalizedData($request);

        Project::query()->create($data);

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'پروژه با موفقیت ساخته شد.');
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.edit', compact('project'));
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $data = $this->normalizedData($request, $project);

        $project->update($data);

        return redirect()
            ->route('admin.projects.edit', $project)
            ->with('success', 'تغییرات پروژه ذخیره شد.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'پروژه حذف شد.');
    }

    private function normalizedData(ProjectRequest $request, ?Project $project = null): array
    {
        $data = $request->validated();
        $removeCoverImage = (bool) ($data['remove_cover_image'] ?? false);

        unset($data['remove_cover_image']);

        if ($request->hasFile('cover_image')) {
            if ($project?->cover_image) {
                Storage::disk('public')->delete($project->cover_image);
            }

            $data['cover_image'] = $request->file('cover_image')->store('projects', 'public');
        } elseif ($removeCoverImage) {
            if ($project?->cover_image) {
                Storage::disk('public')->delete($project->cover_image);
            }

            $data['cover_image'] = null;
        } else {
            unset($data['cover_image']);
        }

        if (($data['is_published'] ?? false) && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
