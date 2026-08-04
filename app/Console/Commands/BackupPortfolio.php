<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupPortfolio extends Command
{
    protected $signature = 'portfolio:backup
        {--database-only : Skip public uploaded media}
        {--keep= : Number of backup archives to retain}';

    protected $description = 'Create a database and uploaded-media backup for the portfolio.';

    public function handle(): int
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupRoot = (string) config('production.backup.path', storage_path('app/backups'));
        $temporary = $backupRoot.DIRECTORY_SEPARATOR.'.tmp-'.$timestamp;
        $archive = $backupRoot.DIRECTORY_SEPARATOR.'portfolio-'.$timestamp.'.zip';

        File::ensureDirectoryExists($backupRoot);
        File::ensureDirectoryExists($temporary);

        try {
            $databaseFile = $this->backupDatabase($temporary);

            if (! $this->option('database-only')) {
                $this->backupMedia($temporary);
            }

            File::put($temporary.DIRECTORY_SEPARATOR.'manifest.json', json_encode([
                'application' => config('app.name'),
                'created_at' => now()->toIso8601String(),
                'environment' => app()->environment(),
                'version' => config('production.version'),
                'commit' => config('production.commit_sha'),
                'database_connection' => config('database.default'),
                'database_file' => basename($databaseFile),
                'includes_media' => ! $this->option('database-only'),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $this->createArchive($temporary, $archive);
            File::deleteDirectory($temporary);
            $this->prune((int) ($this->option('keep') ?: config('production.backup.keep', 14)), $backupRoot);

            $this->info("Backup created: {$archive}");

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            File::deleteDirectory($temporary);
            $this->error('Backup failed: '.$exception->getMessage());
            report($exception);

            return self::FAILURE;
        }
    }

    private function backupDatabase(string $temporary): string
    {
        $connection = (string) config('database.default');
        $config = config("database.connections.{$connection}", []);
        $driver = $config['driver'] ?? $connection;

        return match ($driver) {
            'sqlite' => $this->backupSqlite($config, $temporary),
            'mysql', 'mariadb' => $this->backupMysql($config, $temporary),
            'pgsql' => $this->backupPostgres($config, $temporary),
            default => throw new \RuntimeException("Unsupported database driver: {$driver}"),
        };
    }

    private function backupSqlite(array $config, string $temporary): string
    {
        $source = (string) ($config['database'] ?? '');

        if ($source === ':memory:' || ! File::exists($source)) {
            throw new \RuntimeException('SQLite database file does not exist.');
        }

        $destination = $temporary.DIRECTORY_SEPARATOR.'database.sqlite';

        if (class_exists(\SQLite3::class)) {
            $sourceDatabase = new \SQLite3($source, SQLITE3_OPEN_READONLY);
            $destinationDatabase = new \SQLite3($destination);

            try {
                if (! $sourceDatabase->backup($destinationDatabase)) {
                    throw new \RuntimeException('SQLite online backup failed.');
                }
            } finally {
                $sourceDatabase->close();
                $destinationDatabase->close();
            }

            return $destination;
        }

        if (! File::copy($source, $destination)) {
            throw new \RuntimeException('Could not copy SQLite database.');
        }

        return $destination;
    }

    private function backupMysql(array $config, string $temporary): string
    {
        $destination = $temporary.DIRECTORY_SEPARATOR.'database.sql';
        $command = [
            (string) config('production.backup.mysqldump_binary', 'mysqldump'),
            '--host='.(string) ($config['host'] ?? '127.0.0.1'),
            '--port='.(string) ($config['port'] ?? 3306),
            '--user='.(string) ($config['username'] ?? ''),
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--no-tablespaces',
            '--default-character-set=utf8mb4',
            (string) ($config['database'] ?? ''),
        ];

        $process = new Process($command, null, [
            'MYSQL_PWD' => (string) ($config['password'] ?? ''),
        ]);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput()) ?: 'mysqldump failed.');
        }

        File::put($destination, $process->getOutput());

        return $destination;
    }

    private function backupPostgres(array $config, string $temporary): string
    {
        $destination = $temporary.DIRECTORY_SEPARATOR.'database.sql';
        $command = [
            (string) config('production.backup.pg_dump_binary', 'pg_dump'),
            '--host='.(string) ($config['host'] ?? '127.0.0.1'),
            '--port='.(string) ($config['port'] ?? 5432),
            '--username='.(string) ($config['username'] ?? ''),
            '--format=plain',
            '--no-owner',
            '--no-privileges',
            (string) ($config['database'] ?? ''),
        ];

        $process = new Process($command, null, [
            'PGPASSWORD' => (string) ($config['password'] ?? ''),
        ]);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput()) ?: 'pg_dump failed.');
        }

        File::put($destination, $process->getOutput());

        return $destination;
    }

    private function backupMedia(string $temporary): void
    {
        $source = storage_path('app/public');

        if (! File::isDirectory($source)) {
            return;
        }

        $destination = $temporary.DIRECTORY_SEPARATOR.'public-media';

        if (! File::copyDirectory($source, $destination)) {
            throw new \RuntimeException('Could not copy uploaded media.');
        }
    }

    private function createArchive(string $source, string $archive): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new \RuntimeException('PHP Zip extension is required to create backups.');
        }

        $zip = new ZipArchive();

        if ($zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create ZIP archive.');
        }

        foreach (File::allFiles($source) as $file) {
            $zip->addFile($file->getPathname(), $file->getRelativePathname());
        }

        $zip->close();
    }

    private function prune(int $keep, string $backupRoot): void
    {
        $keep = max(1, $keep);

        $archives = collect(File::glob($backupRoot.DIRECTORY_SEPARATOR.'portfolio-*.zip') ?: [])
            ->sortByDesc(fn (string $path): int => File::lastModified($path))
            ->values();

        $archives->slice($keep)->each(fn (string $path): bool => File::delete($path));
    }
}
