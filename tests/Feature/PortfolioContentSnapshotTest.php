<?php

namespace Tests\Feature;

use App\Models\Project;
use Database\Seeders\ExperienceSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\SiteSettingSeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortfolioContentSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private string $snapshotPath;

    private string $mediaPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->snapshotPath = storage_path('app/testing-content/portfolio.json');
        $this->mediaPath = storage_path('app/testing-content/media');

        File::deleteDirectory(dirname($this->snapshotPath));
        Storage::fake('public');

        $this->seed([
            ProjectSeeder::class,
            SkillSeeder::class,
            ExperienceSeeder::class,
            SiteSettingSeeder::class,
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(dirname($this->snapshotPath));
        parent::tearDown();
    }

    public function test_public_content_can_be_exported_and_restored_without_private_data(): void
    {
        Storage::disk('public')->put('projects/demian/cover.txt', 'media');

        $project = Project::query()->where('slug', 'demian-arcade')->firstOrFail();
        $originalTitle = $project->title;
        $project->update(['cover_image' => 'projects/demian/cover.txt']);

        $this->artisan('portfolio:content-export', [
            '--path' => $this->snapshotPath,
            '--media' => $this->mediaPath,
        ])->assertSuccessful();

        $this->assertFileExists($this->snapshotPath);
        $this->assertFileExists($this->mediaPath.'/projects/demian/cover.txt');

        $json = File::get($this->snapshotPath);
        $this->assertStringNotContainsString('password', $json);
        $this->assertStringNotContainsString('contact_messages', $json);
        $this->assertStringNotContainsString('activity_logs', $json);

        $project->update(['title' => 'Changed locally']);
        Storage::disk('public')->delete('projects/demian/cover.txt');

        $this->artisan('portfolio:content-import', [
            '--path' => $this->snapshotPath,
            '--media' => $this->mediaPath,
            '--replace' => true,
            '--force' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('projects', [
            'slug' => 'demian-arcade',
            'title' => $originalTitle,
        ]);
        Storage::disk('public')->assertExists('projects/demian/cover.txt');
    }
}
