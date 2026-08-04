<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\PortfolioContentSnapshot;
use Illuminate\Console\Command;

class ExportPortfolioContent extends Command
{
    protected $signature = 'portfolio:content-export
        {--path=content/portfolio.json : JSON snapshot path}
        {--media=content/media : Committed media snapshot directory}
        {--seed-if-empty : Seed the demo portfolio only when no projects exist}';

    protected $description = 'Export CMS-managed public portfolio content into a safe, version-controlled snapshot.';

    public function handle(PortfolioContentSnapshot $snapshot): int
    {
        if ($this->option('seed-if-empty') && ! Project::query()->exists()) {
            $this->components->warn('No projects found; seeding the initial portfolio content.');
            $this->call('db:seed', ['--force' => true]);
        }

        $result = $snapshot->export(
            $this->absolutePath((string) $this->option('path')),
            $this->absolutePath((string) $this->option('media')),
        );

        $this->components->info('Portfolio content snapshot created.');
        $this->table(['Item', 'Count'], [
            ['Projects', $result['projects']],
            ['Skills', $result['skills']],
            ['Experiences', $result['experiences']],
            ['Settings', $result['settings']],
        ]);
        $this->line('JSON: '.$result['json_path']);
        $this->line('Media: '.$result['media_path']);

        return self::SUCCESS;
    }

    private function absolutePath(string $path): string
    {
        return str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $path)
            ? $path
            : base_path($path);
    }
}
