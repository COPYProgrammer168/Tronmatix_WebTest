<?php

namespace Tests\Feature\Settings;

use App\Models\Admin;
use App\Models\AdminSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_visit_the_settings_page()
    {
        // Setup necessary data that the view might expect
        // The view uses `App\Models\AdminSetting::str` and expects `$settings`, `$counts`, `$marqueeMessages`
        
        $admin = Admin::factory()->create(['role' => 'superadmin']);
        $this->actingAs($admin, 'admin');

        // We need to mock the view data or have it in the DB to avoid errors
        // Given the view structure, it seems to expect certain variables
        // If it crashes, I will need to provide them in the controller test or factory
        
        $response = $this->get(route('dashboard.settings'));

        $response->assertOk();
    }
}
