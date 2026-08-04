<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SiteSetting::definitions() as $index => $definition) {
            $setting = SiteSetting::query()->firstOrNew(['key' => $index]);

            if (! $setting->exists) {
                $setting->value = $definition['default'] ?? null;
            }

            $setting->fill([
                'label' => $definition['label'],
                'group' => $definition['group'],
                'type' => $definition['type'],
                'sort_order' => array_search($index, array_keys(SiteSetting::definitions()), true),
            ])->save();
        }

        SiteSetting::forgetCache();
    }
}
