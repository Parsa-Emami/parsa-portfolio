<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExperienceRequest;
use App\Models\Experience;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExperienceController extends Controller
{
    public function index(): View
    {
        return view('admin.experiences.index', [
            'experiences' => Experience::query()->orderBy('sort_order')->orderByDesc('started_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.experiences.create', [
            'experience' => new Experience(['sort_order' => 0, 'is_published' => true]),
        ]);
    }

    public function store(ExperienceRequest $request): RedirectResponse
    {
        Experience::query()->create($this->data($request));
        return redirect()->route('admin.experiences.index')->with('success', 'سابقه اضافه شد.');
    }

    public function edit(Experience $experience): View
    {
        return view('admin.experiences.edit', compact('experience'));
    }

    public function update(ExperienceRequest $request, Experience $experience): RedirectResponse
    {
        $experience->update($this->data($request));
        return back()->with('success', 'سابقه ویرایش شد.');
    }

    public function destroy(Experience $experience): RedirectResponse
    {
        $experience->delete();
        return redirect()->route('admin.experiences.index')->with('success', 'سابقه حذف شد.');
    }

    private function data(ExperienceRequest $request): array
    {
        $data = $request->validated();
        if ($data['is_current'] ?? false) {
            $data['ended_at'] = null;
        }
        return $data;
    }
}
