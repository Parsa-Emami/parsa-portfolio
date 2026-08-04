<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check(fn () => DB::select('select 1')),
            'cache' => $this->check(function (): void {
                $key = 'health:'.Str::uuid();
                Cache::put($key, 'ok', 30);

                if (Cache::get($key) !== 'ok') {
                    throw new \RuntimeException('Cache write/read failed.');
                }

                Cache::forget($key);
            }),
            'storage' => $this->check(function (): void {
                $path = storage_path('framework');

                if (! File::isDirectory($path) || ! is_writable($path)) {
                    throw new \RuntimeException('Storage is not writable.');
                }
            }),
        ];

        $healthy = collect($checks)->every(fn (array $check): bool => $check['ok']);

        $deployment = $this->deploymentMetadata();

        $payload = [
            'status' => $healthy ? 'ok' : 'degraded',
            'application' => config('app.name'),
            'environment' => app()->environment(),
            'version' => config('production.version'),
            'commit' => config('production.commit_sha') ?: ($deployment['commit'] ?? null),
            'build' => config('production.build_number') ?: ($deployment['build'] ?? null),
            'deployed_at' => $deployment['deployed_at'] ?? null,
            'timestamp' => now()->toIso8601String(),
        ];

        if (config('production.health.expose_details') || ! app()->isProduction()) {
            $payload['checks'] = $checks;
        }

        return response()->json($payload, $healthy ? 200 : 503)
            ->header('Cache-Control', 'no-store, private');
    }

    private function deploymentMetadata(): array
    {
        $path = storage_path('framework/deployment.json');

        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function check(callable $callback): array
    {
        try {
            $callback();

            return ['ok' => true];
        } catch (\Throwable $exception) {
            report($exception);

            return [
                'ok' => false,
                'message' => app()->isProduction() ? 'Unavailable' : $exception->getMessage(),
            ];
        }
    }
}
