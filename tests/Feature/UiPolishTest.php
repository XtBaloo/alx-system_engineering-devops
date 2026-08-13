<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiPolishTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('administrator');

        return $user;
    }

    public function test_data_tables_are_wrapped_for_horizontal_scrolling_on_narrow_screens(): void
    {
        $response = $this->actingAs($this->admin())->get('/students');

        $response->assertOk();
        $response->assertSee('overflow-x-auto', false);
        $response->assertSee('table-base', false);
    }

    public function test_flash_messages_are_hidden_from_print_output(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->from('/settings')->put('/settings', [
            'school_name' => 'Prime Foundation Academy',
            'currency_symbol' => '₦',
            'examination_max_score' => 60,
            'ranking_method' => 'standard_competition',
        ])->assertSessionHas('success');

        // Follow the redirect: the toast rendering the flashed success
        // message must carry print:hidden so it never leaks into a printout.
        $response = $this->actingAs($admin)->get('/settings');

        $response->assertOk();
        $this->assertStringContainsString('print:hidden', $response->getContent());
    }

    public function test_the_mobile_navigation_toggle_has_an_accessible_name(): void
    {
        $response = $this->actingAs($this->admin())->get('/dashboard');

        $response->assertOk();
        $response->assertSee('aria-label="Toggle navigation menu"', false);
    }

    public function test_the_profile_page_shares_the_dashboard_shell_with_the_rest_of_the_app(): void
    {
        $response = $this->actingAs($this->admin())->get('/profile');

        $response->assertOk();
        $response->assertSee('<aside', false);
    }
}
