<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(): View
    {
        $projects = Project::query()
            ->published()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->get();

        return view('portfolio.index', compact('projects'));
    }

    public function show(Project $project): View
    {
        abort_unless(
            $project->is_published && ($project->published_at === null || $project->published_at->isPast()),
            404
        );

        $nextProject = Project::query()
            ->published()
            ->where('id', '!=', $project->id)
            ->orderBy('sort_order')
            ->first();

        return view('portfolio.show', compact('project', 'nextProject'));
    }
}
