<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Laravel', 'category' => 'Backend', 'short_label' => 'LAR', 'proficiency' => 94, 'sort_order' => 1],
            ['name' => 'PHP', 'category' => 'Backend', 'short_label' => 'PHP', 'proficiency' => 92, 'sort_order' => 2],
            ['name' => 'MySQL', 'category' => 'Backend', 'short_label' => 'SQL', 'proficiency' => 88, 'sort_order' => 3],
            ['name' => 'Livewire', 'category' => 'Backend', 'short_label' => 'LW', 'proficiency' => 84, 'sort_order' => 4],
            ['name' => 'JavaScript', 'category' => 'Frontend', 'short_label' => 'JS', 'proficiency' => 88, 'sort_order' => 5],
            ['name' => 'GSAP', 'category' => 'Frontend', 'short_label' => 'GS', 'proficiency' => 80, 'sort_order' => 6],
            ['name' => 'Blade', 'category' => 'Frontend', 'short_label' => 'BLD', 'proficiency' => 94, 'sort_order' => 7],
            ['name' => 'Tailwind CSS', 'category' => 'Frontend', 'short_label' => 'TW', 'proficiency' => 86, 'sort_order' => 8],
            ['name' => 'Git & GitHub', 'category' => 'Workflow', 'short_label' => 'GIT', 'proficiency' => 88, 'sort_order' => 9],
            ['name' => 'System Architecture', 'category' => 'Workflow', 'short_label' => 'SYS', 'proficiency' => 90, 'sort_order' => 10],
            ['name' => 'Docker', 'category' => 'Workflow', 'short_label' => 'DKR', 'proficiency' => 72, 'sort_order' => 11],
            ['name' => 'Game Systems', 'category' => 'Creative Tech', 'short_label' => 'GAME', 'proficiency' => 78, 'sort_order' => 12],
        ];

        foreach ($items as $item) {
            Skill::query()->updateOrCreate(['name' => $item['name']], [...$item, 'is_published' => true]);
        }
    }
}
