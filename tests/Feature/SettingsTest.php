<?php

namespace Tests\Feature;

use App\Models\SchoolSetting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
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

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'school_name' => 'Prime Foundation Academy',
            'currency_symbol' => '₦',
            'examination_max_score' => 60,
            'ranking_method' => 'standard_competition',
        ], $overrides);
    }

    public function test_administrator_can_update_school_settings(): void
    {
        $response = $this->actingAs($this->admin())->put('/settings', $this->validPayload([
            'school_name' => 'Updated Academy Name',
        ]));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertEquals('Updated Academy Name', SchoolSetting::current()->school_name);
    }

    public function test_school_name_is_required(): void
    {
        $response = $this->actingAs($this->admin())->put('/settings', $this->validPayload([
            'school_name' => '',
        ]));

        $response->assertSessionHasErrors('school_name');
        $this->assertNotEquals('', SchoolSetting::current()->school_name);
    }

    public function test_an_invalid_email_is_rejected(): void
    {
        $response = $this->actingAs($this->admin())->put('/settings', $this->validPayload([
            'email' => 'not-an-email',
        ]));

        $response->assertSessionHasErrors('email');
    }

    public function test_examination_max_score_out_of_range_is_rejected(): void
    {
        $response = $this->actingAs($this->admin())->put('/settings', $this->validPayload([
            'examination_max_score' => 150,
        ]));

        $response->assertSessionHasErrors('examination_max_score');
    }

    public function test_validation_errors_are_displayed_inline_on_the_settings_page(): void
    {
        $this->actingAs($this->admin())->put('/settings', $this->validPayload([
            'school_name' => '',
        ]));

        $response = $this->actingAs($this->admin())->get('/settings');

        $response->assertOk();
        $response->assertSee('required', false);
    }
}
