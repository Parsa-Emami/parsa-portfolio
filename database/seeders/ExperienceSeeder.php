<?php

namespace Database\Seeders;

use App\Models\Experience;
use Illuminate\Database\Seeder;

class ExperienceSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title' => 'Laravel Developer & Product Builder',
                'organization' => 'Independent',
                'location' => 'Remote',
                'started_at' => '2023-01-01',
                'is_current' => true,
                'description' => 'Designing and engineering Laravel products, internal tools and interactive web experiences from architecture through deployment.',
                'achievements' => ['Reusable Laravel architectures', 'Admin systems and production deployment', 'Performance-focused interactive interfaces'],
                'sort_order' => 1,
            ],
            [
                'title' => 'Creative Technology & Interactive Systems',
                'organization' => 'Selected projects',
                'location' => 'Hybrid',
                'started_at' => '2024-01-01',
                'is_current' => true,
                'description' => 'Building browser games, visualization tools and motion-rich experiences with maintainability as a core requirement.',
                'achievements' => ['2D game architecture', 'GSAP interaction systems', 'Data-driven visual interfaces'],
                'sort_order' => 2,
            ],
        ];

        foreach ($items as $item) {
            Experience::query()->updateOrCreate(
                ['title' => $item['title'], 'organization' => $item['organization']],
                [...$item, 'is_published' => true]
            );
        }
    }
}
