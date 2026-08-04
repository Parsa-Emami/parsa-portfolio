<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(18));

        $request->attributes->set('csp_nonce', $nonce);
        View::share('cspNonce', $nonce);

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-site');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        if ($request->isSecure()) {
            $hsts = 'max-age='.config('production.security.hsts_max_age', 31536000);

            if (config('production.security.hsts_include_subdomains', true)) {
                $hsts .= '; includeSubDomains';
            }

            if (config('production.security.hsts_preload', false)) {
                $hsts .= '; preload';
            }

            $response->headers->set('Strict-Transport-Security', $hsts);
        }

        $mode = config('production.security.csp_mode', 'enforce');

        if (in_array($mode, ['enforce', 'report-only'], true)) {
            $header = $mode === 'report-only'
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy';

            $response->headers->set($header, $this->policy($nonce, app()->isProduction()));
        }

        return $response;
    }

    private function policy(string $nonce, bool $production): string
    {
        $developmentScripts = $production
            ? ''
            : ' http://localhost:* http://127.0.0.1:*';

        $developmentConnections = $production
            ? ''
            : ' http://localhost:* http://127.0.0.1:* ws://localhost:* ws://127.0.0.1:*';

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
            "script-src 'self' 'nonce-{$nonce}' https://www.googletagmanager.com{$developmentScripts}",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "font-src 'self' data: https://fonts.bunny.net",
            "img-src 'self' data: blob: https:",
            "media-src 'self' blob: https:",
            "connect-src 'self' https://www.googletagmanager.com https://*.google-analytics.com{$developmentConnections}",
            "frame-src https://www.youtube-nocookie.com https://player.vimeo.com",
            "worker-src 'self' blob:",
            "manifest-src 'self'",
        ];

        if ($production) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives).';';
    }
}
