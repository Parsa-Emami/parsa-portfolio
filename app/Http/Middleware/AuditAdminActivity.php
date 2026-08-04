<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditAdminActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $response = $next($request);

        if (! $user || ! $user->is_admin || $request->isMethodSafe()) {
            return $response;
        }

        try {
            ActivityLog::query()->create([
                'user_id' => $user->getKey(),
                'request_id' => (string) $request->attributes->get('request_id'),
                'route_name' => $request->route()?->getName(),
                'method' => $request->method(),
                'path' => mb_substr($request->path(), 0, 500),
                'action' => $this->action($request),
                'status_code' => $response->getStatusCode(),
                'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
                'user_agent_hash' => hash('sha256', (string) $request->userAgent()),
                'payload_keys' => $this->payloadKeys($request->except([
                    '_token',
                    '_method',
                    'password',
                    'password_confirmation',
                    'current_password',
                    'reply_message',
                ])),
                'created_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Admin activity could not be recorded.', [
                'exception' => $exception->getMessage(),
                'request_id' => $request->attributes->get('request_id'),
            ]);
        }

        return $response;
    }

    private function action(Request $request): string
    {
        return trim(($request->route()?->getName() ?: 'admin.request').' '.$request->method());
    }

    private function payloadKeys(array $payload): array
    {
        $keys = [];

        array_walk_recursive($payload, function (mixed $value, string|int $key) use (&$keys): void {
            if (is_string($key)) {
                $keys[] = $key;
            }
        });

        return array_values(array_unique(array_slice($keys, 0, 100)));
    }
}
