<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_is_available(): void
    {
        $response = $this->get(route('portfolio.index'));

        $response
            ->assertOk()
            ->assertSee('I build digital');
    }

    public function test_published_project_is_visible_and_has_a_case_study(): void
    {
        $project = Project::query()->create([
            'title' => 'Test Project',
            'summary' => 'A test project summary.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->get(route('portfolio.index'))
            ->assertOk()
            ->assertSee('Test Project');

        $this->get(route('portfolio.projects.show', $project))
            ->assertOk()
            ->assertSee('A test project summary.');
    }

    public function test_unpublished_project_returns_not_found(): void
    {
        $project = Project::query()->create([
            'title' => 'Private Project',
            'summary' => 'Not public yet.',
            'is_published' => false,
        ]);

        $this->get(route('portfolio.projects.show', $project))
            ->assertNotFound();
    }
}
