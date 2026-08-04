<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SkillRequest;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SkillController extends Controller
{
    public function index(): View
    {
        return view('admin.skills.index', [
            'skills' => Skill::query()->orderBy('sort_order')->orderBy('name')->get(),
            'skill' => new Skill(['sort_order' => 0, 'is_published' => true, 'category' => 'Development']),
        ]);
    }

    public function store(SkillRequest $request): RedirectResponse
    {
        Skill::query()->create($request->validated());
        return back()->with('success', 'مهارت اضافه شد.');
    }

    public function update(SkillRequest $request, Skill $skill): RedirectResponse
    {
        $skill->update($request->validated());
        return back()->with('success', 'مهارت ویرایش شد.');
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $skill->delete();
        return back()->with('success', 'مهارت حذف شد.');
    }
}
