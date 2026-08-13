<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/students')->assertRedirect('/login');
    }

    public function test_super_admin_can_access_every_administrative_area(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)->get('/students')->assertOk();
        $this->actingAs($user)->get('/users')->assertOk();
        $this->actingAs($user)->get('/settings')->assertOk();
        $this->actingAs($user)->get('/audit-logs')->assertOk();
    }

    public function test_student_cannot_access_admin_only_areas(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user)->get('/students')->assertForbidden();
        $this->actingAs($user)->get('/users')->assertForbidden();
        $this->actingAs($user)->get('/settings')->assertForbidden();
        $this->actingAs($user)->get('/audit-logs')->assertForbidden();
    }

    public function test_teacher_can_view_students_but_not_settings_or_users(): void
    {
        $user = User::factory()->create();
        $user->assignRole('teacher');

        $this->actingAs($user)->get('/students')->assertOk();
        $this->actingAs($user)->get('/settings')->assertForbidden();
        $this->actingAs($user)->get('/users')->assertForbidden();
    }

    public function test_parent_cannot_access_teacher_or_admin_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('parent');

        $this->actingAs($user)->get('/attendance/take')->assertForbidden();
        $this->actingAs($user)->get('/teachers')->assertForbidden();
    }

    public function test_administrator_can_manage_guardians_but_teacher_cannot(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        $this->actingAs($admin)->get('/guardians')->assertOk();
        $this->actingAs($admin)->get('/guardians/create')->assertOk();

        $this->actingAs($teacher)->get('/guardians')->assertForbidden();
        $this->actingAs($teacher)->get('/guardians/create')->assertForbidden();
    }

    public function test_only_super_admin_or_administrator_can_reopen_a_closed_term(): void
    {
        $session = AcademicSession::factory()->create();
        $term = Term::factory()->create([
            'academic_session_id' => $session->id,
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');
        $this->actingAs($teacher)->post("/terms/{$term->id}/reopen")->assertForbidden();
        $this->assertEquals('closed', $term->fresh()->status);

        $admin = User::factory()->create();
        $admin->assignRole('administrator');
        $this->actingAs($admin)->post("/terms/{$term->id}/reopen")->assertRedirect();
        $this->assertEquals('open', $term->fresh()->status);
    }
}
