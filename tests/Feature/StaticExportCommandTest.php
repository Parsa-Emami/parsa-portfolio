<?php

namespace Tests\Feature;

use Database\Seeders\ExperienceSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\SiteSettingSeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StaticExportCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $outputPath;

    private bool $createdViteBuild = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->outputPath = storage_path('app/testing-static-export');
        File::deleteDirectory($this->outputPath);

        config(['portfolio.email' => 'hello@example.com']);

        $this->seed([
            ProjectSeeder::class,
            SkillSeeder::class,
            ExperienceSeeder::class,
            SiteSettingSeeder::class,
        ]);

        $this->ensureTestViteBuildExists();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->outputPath);

        if ($this->createdViteBuild) {
            File::deleteDirectory(public_path('build'));
        }

        parent::tearDown();
    }

    public function test_it_exports_a_complete_github_pages_site(): void
    {
        $baseUrl = 'https://example.github.io/parsa-portfolio';

        $this->artisan('portfolio:export-static', [
            '--output' => $this->outputPath,
            '--base-url' => $baseUrl,
        ])->assertSuccessful();

        $this->assertFileExists($this->outputPath.'/index.html');
        $this->assertFileExists($this->outputPath.'/404.html');
        $this->assertFileExists($this->outputPath.'/sitemap.xml');
        $this->assertFileExists($this->outputPath.'/site.webmanifest');
        $this->assertFileExists($this->outputPath.'/.nojekyll');
        $this->assertFileExists($this->outputPath.'/projects/demian-arcade/index.html');
        $this->assertFileExists($this->outputPath.'/build/assets/app.css');

        $home = File::get($this->outputPath.'/index.html');
        $manifest = json_decode(File::get($this->outputPath.'/site.webmanifest'), true, flags: JSON_THROW_ON_ERROR);

        $this->assertStringContainsString($baseUrl.'/build/assets/app.css', $home);
        $this->assertStringContainsString('data-static-contact-form', $home);
        $this->assertStringNotContainsString($baseUrl.'/contact', $home);
        $this->assertSame($baseUrl, $manifest['start_url']);
        $this->assertSame($baseUrl.'/', $manifest['scope']);
    }

    private function ensureTestViteBuildExists(): void
    {
        if (File::exists(public_path('build/manifest.json'))) {
            return;
        }

        $this->createdViteBuild = true;

        File::ensureDirectoryExists(public_path('build/assets'));
        File::put(public_path('build/assets/app.css'), 'body{}');
        File::put(public_path('build/assets/app.js'), 'console.log("test");');
        File::put(public_path('build/manifest.json'), json_encode([
            'resources/css/app.css' => [
                'file' => 'assets/app.css',
                'src' => 'resources/css/app.css',
                'isEntry' => true,
            ],
            'resources/js/app.js' => [
                'file' => 'assets/app.js',
                'src' => 'resources/js/app.js',
                'isEntry' => true,
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
