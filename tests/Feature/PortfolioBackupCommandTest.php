<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PortfolioBackupCommandTest extends TestCase
{
    public function test_sqlite_database_only_backup_creates_a_zip_archive(): void
    {
        if (! class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is unavailable.');
        }

        $database = storage_path('framework/testing-backup.sqlite');
        $backups = storage_path('framework/testing-backups');

        File::delete($database);
        File::deleteDirectory($backups);
        File::put($database, '');

        config()->set('database.default', 'backup_testing');
        config()->set('database.connections.backup_testing', [
            'driver' => 'sqlite',
            'database' => $database,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('production.backup.path', $backups);

        DB::purge('backup_testing');
        DB::connection('backup_testing')->statement('create table example (id integer primary key, name text)');
        DB::connection('backup_testing')->table('example')->insert(['name' => 'Portfolio']);

        $exitCode = Artisan::call('portfolio:backup', [
            '--database-only' => true,
            '--keep' => 2,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertCount(1, File::glob($backups.DIRECTORY_SEPARATOR.'portfolio-*.zip') ?: []);

        DB::purge('backup_testing');
        File::delete($database);
        File::deleteDirectory($backups);
    }
}
