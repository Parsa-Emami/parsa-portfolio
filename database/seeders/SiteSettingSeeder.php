<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SiteSetting::definitions() as $index => $definition) {
            SiteSetting::query()->firstOrCreate(
                ['key' => $index],
                [
                    'label' => $definition['label'],
                    'value' => $definition['default'] ?? null,
                    'group' => $definition['group'],
                    'type' => $definition['type'],
                    'sort_order' => array_search($index, array_keys(SiteSetting::definitions()), true),
                ]
            );
        }

        SiteSetting::forgetCache();
    }
}
