<?php

namespace App\Console\Commands;

use App\Services\PortfolioContentSnapshot;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class ImportPortfolioContent extends Command
{
    use ConfirmableTrait;

    protected $signature = 'portfolio:content-import
        {--path=content/portfolio.json : JSON snapshot path}
        {--media=content/media : Committed media snapshot directory}
        {--replace : Replace existing portfolio projects, skills, experiences and settings}
        {--force : Run without confirmation in production}';

    protected $description = 'Import a committed public portfolio content snapshot into the database.';

    public function handle(PortfolioContentSnapshot $snapshot): int
    {
        if (! $this->confirmToProceed('Importing the portfolio content snapshot')) {
            return self::FAILURE;
        }

        $result = $snapshot->import(
            $this->absolutePath((string) $this->option('path')),
            $this->absolutePath((string) $this->option('media')),
            (bool) $this->option('replace'),
        );

        $this->components->info('Portfolio content snapshot imported.');
        $this->table(['Item', 'Count'], [
            ['Projects', $result['projects']],
            ['Skills', $result['skills']],
            ['Experiences', $result['experiences']],
            ['Settings', $result['settings']],
        ]);

        return self::SUCCESS;
    }

    private function absolutePath(string $path): string
    {
        return str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $path)
            ? $path
            : base_path($path);
    }
}
