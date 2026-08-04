<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'definitions' => SiteSetting::definitions(),
            'values' => SiteSetting::values(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $definitions = SiteSetting::definitions();
        $rules = [];

        foreach ($definitions as $key => $definition) {
            $rules[$key] = match ($definition['type']) {
                'email' => ['nullable', 'email:rfc', 'max:255'],
                'url' => ['nullable', 'url', 'max:500'],
                default => ['nullable', 'string', 'max:5000'],
            };
        }

        $validated = $request->validate($rules);

        foreach ($definitions as $key => $definition) {
            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'label' => $definition['label'],
                    'value' => $validated[$key] ?? null,
                    'group' => $definition['group'],
                    'type' => $definition['type'],
                    'sort_order' => array_search($key, array_keys($definitions), true),
                ]
            );
        }

        SiteSetting::forgetCache();

        return back()->with('success', 'تنظیمات سایت ذخیره شد.');
    }
}
