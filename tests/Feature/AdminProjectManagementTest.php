<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_project(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.projects.store'), [
            'title' => 'New Portfolio Project',
            'eyebrow' => 'Laravel platform',
            'summary' => 'A sufficiently detailed project summary for the portfolio.',
            'content' => 'Longer project overview.',
            'role' => 'Full-stack development',
            'year' => now()->year,
            'technologies' => 'Laravel, Blade, MySQL',
            'accent' => '#d7ff3f',
            'is_featured' => '1',
            'is_published' => '1',
            'sort_order' => 4,
        ]);

        $project = Project::query()
            ->where('title', 'New Portfolio Project')
            ->firstOrFail();

        $response->assertRedirect(route('admin.projects.edit', $project));

        $this->assertSame(['Laravel', 'Blade', 'MySQL'], $project->technologies);
        $this->assertTrue($project->is_published);
        $this->assertNotNull($project->published_at);
    }

    public function test_non_admin_receives_forbidden_response(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.projects.index'))
            ->assertForbidden();
    }
}
