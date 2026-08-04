<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_responses_include_security_and_request_headers(): void
    {
        config()->set('production.security.csp_mode', 'enforce');

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $this->assertNotEmpty($response->headers->get('X-Request-ID'));
        $this->assertStringContainsString(
            "default-src 'self'",
            (string) $response->headers->get('Content-Security-Policy')
        );
    }

    public function test_a_valid_incoming_request_id_is_preserved(): void
    {
        $requestId = 'portfolio-test-request-1234';

        $this->withHeader('X-Request-ID', $requestId)
            ->get('/')
            ->assertHeader('X-Request-ID', $requestId);
    }
}
