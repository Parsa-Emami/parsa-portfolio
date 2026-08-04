<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\ImageOptimizerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function __construct(private readonly ImageOptimizerService $images) {}

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
                'url' => ['nullable', 'url:http,https', 'max:500'],
                'boolean' => ['nullable', 'boolean'],
                'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
                'file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
                default => ['nullable', 'string', 'max:5000'],
            };
            if (in_array($definition['type'], ['image', 'file'], true)) {
                $rules["remove_{$key}"] = ['nullable', 'boolean'];
            }
        }

        $validated = $request->validate($rules);
        $current = SiteSetting::values();

        foreach ($definitions as $key => $definition) {
            $value = $validated[$key] ?? ($current[$key] ?? null);

            if ($definition['type'] === 'boolean') {
                $value = $request->boolean($key) ? '1' : '0';
            }

            if ($definition['type'] === 'image') {
                if ($request->hasFile($key)) {
                    $this->images->delete($current[$key] ?? null);
                    $value = $this->images->store($request->file($key), 'site', 1800)['path'];
                } elseif ($request->boolean("remove_{$key}")) {
                    $this->images->delete($current[$key] ?? null);
                    $value = null;
                }
            }

            if ($definition['type'] === 'file') {
                if ($request->hasFile($key)) {
                    Storage::disk('public')->delete($current[$key] ?? '');
                    $value = $request->file($key)->store('site', 'public');
                } elseif ($request->boolean("remove_{$key}")) {
                    Storage::disk('public')->delete($current[$key] ?? '');
                    $value = null;
                }
            }

            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'label' => $definition['label'],
                    'value' => $value,
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
