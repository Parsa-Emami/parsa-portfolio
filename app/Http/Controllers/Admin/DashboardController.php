<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Project;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'projectCount' => Project::query()->count(),
            'publishedProjectCount' => Project::query()->where('is_published', true)->count(),
            'messageCount' => ContactMessage::query()->count(),
            'unreadMessageCount' => ContactMessage::query()->unread()->count(),
            'latestMessages' => ContactMessage::query()->latest()->limit(5)->get(),
        ]);
    }
}
