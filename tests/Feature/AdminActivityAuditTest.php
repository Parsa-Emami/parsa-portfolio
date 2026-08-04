<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminActivityAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_mutating_admin_requests_are_audited_without_storing_values(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.logout'), [
            'secret_field' => 'must-not-be-stored',
        ]);

        $response->assertRedirect();

        $log = ActivityLog::query()->latestFirst()->firstOrFail();

        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame('POST', $log->method);
        $this->assertSame('admin.logout', $log->route_name);
        $this->assertContains('secret_field', $log->payload_keys);
        $this->assertStringNotContainsString('must-not-be-stored', $log->toJson());
    }

    public function test_activity_page_is_admin_only(): void
    {
        $this->get(route('admin.activity.index'))
            ->assertRedirect(route('admin.login'));

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.activity.index'))
            ->assertOk();
    }
}
