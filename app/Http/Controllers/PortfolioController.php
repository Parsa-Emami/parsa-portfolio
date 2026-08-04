<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\Skill;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(): View
    {
        return view('portfolio.index', [
            'projects' => Project::query()
                ->published()
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderByDesc('published_at')
                ->get(),
            'skills' => Skill::query()->published()->orderBy('sort_order')->orderBy('name')->get()->groupBy('category'),
            'experiences' => Experience::query()->published()->orderBy('sort_order')->orderByDesc('started_at')->get(),
            'settings' => SiteSetting::values(),
        ]);
    }

    public function show(Project $project): View
    {
        abort_unless(
            $project->is_published && ($project->published_at === null || $project->published_at->isPast()),
            404
        );

        $project->load('media');
        $ordered = Project::query()->published()->orderBy('sort_order')->orderByDesc('published_at')->get();
        $index = $ordered->search(fn (Project $item) => $item->is($project));
        $nextProject = $ordered->count() > 1 ? $ordered->get(($index + 1) % $ordered->count()) : null;
        $previousProject = $ordered->count() > 1 ? $ordered->get(($index - 1 + $ordered->count()) % $ordered->count()) : null;

        return view('portfolio.show', [
            'project' => $project,
            'nextProject' => $nextProject,
            'previousProject' => $previousProject,
            'settings' => SiteSetting::values(),
        ]);
    }
}
