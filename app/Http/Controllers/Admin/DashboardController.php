<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'projectCount' => Project::query()->count(),
            'publishedProjectCount' => Project::query()->where('is_published', true)->count(),
            'mediaCount' => Project::query()->withCount('media')->get()->sum('media_count'),
            'skillCount' => Skill::query()->count(),
            'experienceCount' => Experience::query()->count(),
            'messageCount' => ContactMessage::query()->count(),
            'unreadMessageCount' => ContactMessage::query()->unread()->count(),
            'latestMessages' => ContactMessage::query()->latest()->limit(5)->get(),
        ]);
    }
}
