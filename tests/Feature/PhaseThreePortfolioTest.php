<?php

namespace Tests\Feature;

use App\Models\Experience;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseThreePortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_published_skills_and_experience(): void
    {
        Skill::query()->create([
            'name' => 'Laravel Architecture',
            'category' => 'Backend',
            'sort_order' => 1,
            'is_published' => true,
        ]);

        Experience::query()->create([
            'title' => 'Product Engineer',
            'organization' => 'Independent',
            'started_at' => '2024-01-01',
            'is_current' => true,
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $this->get(route('portfolio.index'))
            ->assertOk()
            ->assertSee('Laravel Architecture')
            ->assertSee('Product Engineer')
            ->assertSee('application/ld+json', false);
    }

    public function test_case_study_displays_narrative_and_seo_metadata(): void
    {
        $project = Project::query()->create([
            'title' => 'Advanced Case Study',
            'summary' => 'A detailed project summary for the enhanced portfolio.',
            'challenge' => 'A difficult product challenge.',
            'solution' => 'A maintainable technical solution.',
            'architecture' => 'A modular Laravel architecture.',
            'results' => 'A measurable project outcome.',
            'seo_title' => 'Advanced Case Study SEO',
            'seo_description' => 'A specific search description for this project.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->get(route('portfolio.projects.show', $project))
            ->assertOk()
            ->assertSee('Advanced Case Study SEO')
            ->assertSee('A difficult product challenge.')
            ->assertSee('A modular Laravel architecture.')
            ->assertSee('A measurable project outcome.');
    }

    public function test_sitemap_contains_public_projects_only(): void
    {
        $public = Project::query()->create([
            'title' => 'Public Sitemap Project',
            'summary' => 'This public project belongs in the sitemap.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $private = Project::query()->create([
            'title' => 'Private Sitemap Project',
            'summary' => 'This private project must not be indexed.',
            'is_published' => false,
        ]);

        $this->get(route('seo.sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('portfolio.projects.show', $public), false)
            ->assertDontSee(route('portfolio.projects.show', $private), false);
    }
}
