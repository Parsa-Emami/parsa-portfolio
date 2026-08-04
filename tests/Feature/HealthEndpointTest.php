<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_readiness_endpoint_reports_healthy_dependencies(): void
    {
        $response = $this->getJson(route('health.readiness'));

        $response
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure([
                'status',
                'application',
                'environment',
                'version',
                'timestamp',
                'checks' => [
                    'database' => ['ok'],
                    'cache' => ['ok'],
                    'storage' => ['ok'],
                ],
            ])
            ->assertHeader('Cache-Control', 'no-store, private');
    }
}
