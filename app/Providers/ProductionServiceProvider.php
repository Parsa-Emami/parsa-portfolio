<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ProductionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureRateLimiters();
        $this->configureProductionUrls();
        $this->configureOperationalLogging();
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('admin-login', function (Request $request): array {
            $identity = Str::lower((string) $request->input('email')).'|'.$request->ip();

            return [
                Limit::perMinute(5)->by($identity),
                Limit::perHour(30)->by((string) $request->ip()),
            ];
        });

        RateLimiter::for('contact', fn (Request $request): array => [
            Limit::perMinute(5)->by((string) $request->ip()),
            Limit::perHour(20)->by((string) $request->ip()),
        ]);

        RateLimiter::for('admin', fn (Request $request): Limit => Limit::perMinute(120)
            ->by((string) ($request->user()?->getAuthIdentifier() ?: $request->ip())));

        RateLimiter::for('admin-upload', fn (Request $request): Limit => Limit::perMinute(20)
            ->by((string) ($request->user()?->getAuthIdentifier() ?: $request->ip())));

        RateLimiter::for('health', fn (Request $request): Limit => Limit::perMinute(60)
            ->by((string) $request->ip()));
    }

    private function configureProductionUrls(): void
    {
        if (app()->isProduction() && config('production.force_https')) {
            URL::forceScheme('https');
        }
    }

    private function configureOperationalLogging(): void
    {
        Queue::failing(function (JobFailed $event): void {
            Log::error('Queued job failed.', [
                'connection' => $event->connectionName,
                'job' => $event->job->resolveName(),
                'exception' => $event->exception->getMessage(),
            ]);
        });

        $threshold = max(100, (int) config('production.monitoring.slow_query_ms', 750));

        DB::listen(function (QueryExecuted $query) use ($threshold): void {
            if ($query->time < $threshold) {
                return;
            }

            Log::warning('Slow database query detected.', [
                'time_ms' => $query->time,
                'connection' => $query->connectionName,
                'sql' => $query->sql,
            ]);
        });
    }
}
