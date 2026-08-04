<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductionCheck extends Command
{
    protected $signature = 'portfolio:production-check {--strict : Treat warnings as failures}';

    protected $description = 'Validate the portfolio production configuration and runtime dependencies.';

    public function handle(): int
    {
        $checks = [
            $this->check('APP_ENV is production', app()->isProduction(), 'warning'),
            $this->check('APP_DEBUG is disabled in production', ! app()->isProduction() || ! config('app.debug'), 'error'),
            $this->check('APP_KEY exists', filled(config('app.key')), 'error'),
            $this->check('APP_URL uses HTTPS', str_starts_with((string) config('app.url'), 'https://'), 'warning'),
            $this->check('Force HTTPS enabled', (bool) config('production.force_https'), 'warning'),
            $this->check('CSP enabled', config('production.security.csp_mode') !== 'off', 'warning'),
            $this->check('Production mailer configured', config('mail.default') !== 'log', 'warning'),
            $this->check('Admin email configured', filter_var(config('portfolio.admin.email'), FILTER_VALIDATE_EMAIL) !== false, 'warning'),
            $this->runtimeCheck('Database connection', fn () => DB::select('select 1')),
            $this->runtimeCheck('Cache read/write', function (): void {
                Cache::put('production-check', 'ok', 10);

                if (Cache::pull('production-check') !== 'ok') {
                    throw new \RuntimeException('Cache mismatch.');
                }
            }),
            $this->check('Storage framework is writable', File::isDirectory(storage_path('framework')) && is_writable(storage_path('framework')), 'error'),
            $this->check('Public storage link exists', File::exists(public_path('storage')), 'warning'),
            $this->check('Built Vite manifest exists', File::exists(public_path('build/manifest.json')), 'error'),
            $this->check('Deployment metadata exists', ! app()->isProduction() || File::exists(storage_path('framework/deployment.json')), 'warning'),
        ];

        $this->table(
            ['Check', 'Status', 'Severity', 'Details'],
            collect($checks)->map(fn (array $check): array => [
                $check['name'],
                $check['ok'] ? 'PASS' : 'FAIL',
                strtoupper($check['severity']),
                $check['details'] ?? '',
            ])->all()
        );

        $errors = collect($checks)->where('ok', false)->where('severity', 'error')->count();
        $warnings = collect($checks)->where('ok', false)->where('severity', 'warning')->count();

        if ($errors > 0 || ($this->option('strict') && $warnings > 0)) {
            $this->error("Production check failed: {$errors} error(s), {$warnings} warning(s).");

            return self::FAILURE;
        }

        $this->info("Production check passed with {$warnings} warning(s).");

        return self::SUCCESS;
    }

    private function check(string $name, bool $ok, string $severity, ?string $details = null): array
    {
        return compact('name', 'ok', 'severity', 'details');
    }

    private function runtimeCheck(string $name, callable $callback): array
    {
        try {
            $callback();

            return $this->check($name, true, 'error');
        } catch (\Throwable $exception) {
            return $this->check($name, false, 'error', $exception->getMessage());
        }
    }
}
