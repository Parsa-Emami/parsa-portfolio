<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_portfolio_copy(): void
    {
        $this->seed(SiteSettingSeeder::class);
        $admin = User::factory()->create(['is_admin' => true]);
        $payload = SiteSetting::values();
        $payload['hero_line_1'] = 'I create robust';

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $payload)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('site_settings', [
            'key' => 'hero_line_1',
            'value' => 'I create robust',
        ]);
    }
}
