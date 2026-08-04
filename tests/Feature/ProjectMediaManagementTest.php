<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectMediaManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_and_delete_project_gallery_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $project = Project::query()->create([
            'title' => 'Gallery Project',
            'summary' => 'A project with a managed media gallery.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9ZQmcAAAAASUVORK5CYII=');
        $file = UploadedFile::fake()->createWithContent('gallery.png', $png);

        $this->actingAs($admin)->post(route('admin.projects.media.store', $project), [
            'images' => [$file],
            'alt_text' => 'Gallery preview',
            'caption' => 'A gallery image.',
            'display_size' => 'wide',
        ])->assertSessionHas('success');

        $media = $project->media()->firstOrFail();
        $this->assertSame('Gallery preview', $media->alt_text);
        $this->assertSame('wide', $media->display_size);
        Storage::disk('public')->assertExists($media->path);

        $this->actingAs($admin)
            ->delete(route('admin.projects.media.destroy', [$project, $media]))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('project_media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($media->path);
    }
}
