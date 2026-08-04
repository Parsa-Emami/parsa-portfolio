<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'title' => 'Demian Arcade',
                'slug' => 'demian-arcade',
                'eyebrow' => 'Open-world pixel experience',
                'summary' => 'A modular 2D pixel-art game world built around a living café, reusable game modes and an expandable scene architecture.',
                'content' => "Demian is an experimental browser-based arcade and open-world experience. The project focuses on reusable scene systems, predictable rendering, modular game modes and a café environment that can grow without rewriting the entire game.\n\nThe engineering goal is to keep rendering, input, world data and game rules independent so each part can evolve safely.",
                'role' => 'Architecture & Development',
                'year' => 2026,
                'technologies' => ['JavaScript', 'Canvas', 'Pixel Art', 'GitHub Actions'],
                'accent' => '#d7ff3f',
                'github_url' => 'https://github.com/Parsa-Emami/Demian',
                'is_featured' => true,
                'is_published' => true,
                'sort_order' => 1,
                'published_at' => now(),
            ],
            [
                'title' => 'Hanna Music Player',
                'slug' => 'hanna-music-player',
                'eyebrow' => 'Laravel music platform',
                'summary' => 'A Laravel-powered music application with a focused listening experience, content management and a maintainable backend structure.',
                'content' => "Hanna Music Player explores how a focused media experience can stay fast and manageable while the catalogue grows.\n\nThe application combines a Laravel backend, Blade UI and a structured content workflow for music, artwork and playback-related data.",
                'role' => 'Full-stack Development',
                'year' => 2026,
                'technologies' => ['Laravel', 'PHP', 'Blade', 'MySQL', 'Vite'],
                'accent' => '#ff7b66',
                'github_url' => 'https://github.com/Parsa-Emami/Hanna-Music-Player',
                'is_featured' => true,
                'is_published' => true,
                'sort_order' => 2,
                'published_at' => now(),
            ],
            [
                'title' => 'Negar Week Planner',
                'slug' => 'negar-week-planner',
                'eyebrow' => 'Simple productivity product',
                'summary' => 'A compact weekly availability planner that turns selected time slots into clean, shareable scheduling text.',
                'content' => "Negar Week Planner is a small productivity tool designed around one clear job: selecting weekly availability and sharing it without ambiguity.\n\nIts intentionally narrow scope keeps the interface approachable while Laravel handles validation, persistence and application structure.",
                'role' => 'Product Design & Development',
                'year' => 2026,
                'technologies' => ['Laravel', 'Blade', 'SQLite', 'JavaScript'],
                'accent' => '#8da7ff',
                'github_url' => 'https://github.com/Parsa-Emami/negar-week-planner',
                'is_featured' => false,
                'is_published' => true,
                'sort_order' => 3,
                'published_at' => now(),
            ],
        ];

        foreach ($projects as $project) {
            Project::query()->updateOrCreate(
                ['slug' => $project['slug']],
                $project
            );
        }
    }
}
