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
                'title' => 'Demian Arcade', 'slug' => 'demian-arcade', 'eyebrow' => 'Open-world pixel experience',
                'summary' => 'A modular 2D pixel-art game world built around a living café, reusable game modes and an expandable scene architecture.',
                'content' => 'Demian is an experimental browser-based arcade and open-world experience designed as a platform rather than a single isolated game.',
                'challenge' => 'The environment, controls and game modes had to remain independent while still sharing one coherent world and rendering pipeline.',
                'solution' => 'A scene-based runtime separates world data, rendering, input and game rules. Assets are loaded through predictable registries and every mode receives the same world contract.',
                'architecture' => 'Screen manager, scene lifecycle, asset registry, world-state services and isolated game-mode modules form the core architecture.',
                'results' => 'The café environment can support new game modes and content without rebuilding the application shell or duplicating rendering logic.',
                'role' => 'Architecture & Development', 'client' => 'Personal R&D', 'year' => 2026,
                'technologies' => ['JavaScript', 'Canvas', 'Pixel Art', 'GitHub Actions'],
                'accent' => '#d7ff3f', 'github_url' => 'https://github.com/Parsa-Emami/Demian',
                'seo_title' => 'Demian Arcade — 2D Pixel Open World',
                'seo_description' => 'Architecture and development of a modular browser-based 2D pixel-art open world and arcade platform.',
                'is_featured' => true, 'is_published' => true, 'sort_order' => 1, 'published_at' => now(),
            ],
            [
                'title' => 'Hanna Music Player', 'slug' => 'hanna-music-player', 'eyebrow' => 'Laravel music platform',
                'summary' => 'A Laravel-powered music application with a focused listening experience, content management and a maintainable backend structure.',
                'content' => 'Hanna Music Player explores how a focused media experience can stay fast and manageable while the catalogue grows.',
                'challenge' => 'Music, artwork and playback metadata needed a clean management workflow without making the public listening interface feel administrative.',
                'solution' => 'The project separates catalogue management from the listening UI and uses explicit domain models for media, artwork and playback data.',
                'architecture' => 'Laravel controllers, service-oriented media handling, Blade components and a structured relational model keep concerns isolated.',
                'results' => 'The catalogue can grow while the interface remains focused, fast and straightforward to maintain.',
                'role' => 'Full-stack Development', 'client' => 'Personal Product', 'year' => 2026,
                'technologies' => ['Laravel', 'PHP', 'Blade', 'MySQL', 'Vite'],
                'accent' => '#ff7b66', 'github_url' => 'https://github.com/Parsa-Emami/Hanna-Music-Player',
                'is_featured' => true, 'is_published' => true, 'sort_order' => 2, 'published_at' => now(),
            ],
            [
                'title' => 'Negar Week Planner', 'slug' => 'negar-week-planner', 'eyebrow' => 'Simple productivity product',
                'summary' => 'A compact weekly availability planner that turns selected time slots into clean, shareable scheduling text.',
                'content' => 'Negar Week Planner is a small productivity tool designed around one clear job: selecting weekly availability and sharing it without ambiguity.',
                'challenge' => 'The workflow needed to stay extremely simple while still validating time selections and producing reliable output.',
                'solution' => 'A narrow product scope, direct interactions and server-side validation remove unnecessary decisions from the user journey.',
                'architecture' => 'Laravel handles validation and persistence while Blade and small JavaScript modules keep the frontend lightweight.',
                'results' => 'Users can prepare and share a complete weekly availability schedule in a few focused steps.',
                'role' => 'Product Design & Development', 'client' => 'Personal Product', 'year' => 2026,
                'technologies' => ['Laravel', 'Blade', 'SQLite', 'JavaScript'],
                'accent' => '#8da7ff', 'github_url' => 'https://github.com/Parsa-Emami/negar-week-planner',
                'is_featured' => false, 'is_published' => true, 'sort_order' => 3, 'published_at' => now(),
            ],
        ];

        foreach ($projects as $project) {
            Project::query()->updateOrCreate(['slug' => $project['slug']], $project);
        }
    }
}
