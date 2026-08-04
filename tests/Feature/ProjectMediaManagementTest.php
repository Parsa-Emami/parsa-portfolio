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

        /*
         * A real, CRC-valid 4x3 PNG fixture. The former 1x1 base64 fixture
         * passed getimagesize() but failed imagecreatefrompng() on Ubuntu's
         * libpng/GD build, which caused the GitHub Actions-only failure.
         */
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAQAAAADCAYAAAC09K7GAAAAFUlEQVR4nGO8/t/+PwMSYGJAAxgCAJY6AxpCYRq+AAAAAElFTkSuQmCC',
            true,
        );

        $this->assertIsString($png);

        $file = UploadedFile::fake()->createWithContent('gallery.png', $png);

        $this->actingAs($admin)
            ->post(route('admin.projects.media.store', $project), [
                'images' => [$file],
                'alt_text' => 'Gallery preview',
                'caption' => 'A gallery image.',
                'display_size' => 'wide',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $media = $project->media()->firstOrFail();

        $this->assertSame('Gallery preview', $media->alt_text);
        $this->assertSame('wide', $media->display_size);
        $this->assertSame(4, $media->width);
        $this->assertSame(3, $media->height);

        Storage::disk('public')->assertExists($media->path);

        $this->actingAs($admin)
            ->delete(route('admin.projects.media.destroy', [$project, $media]))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('project_media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($media->path);

        if ($media->thumbnail_path) {
            Storage::disk('public')->assertMissing($media->thumbnail_path);
        }
    }
}
