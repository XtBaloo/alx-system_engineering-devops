<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\FeeCategory;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureCrudTest extends TestCase
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

    public function test_administrator_can_create_a_class(): void
    {
        $response = $this->actingAs($this->admin())->post('/classes', [
            'name' => 'JSS 4', 'level' => 'junior_secondary', 'order' => 20,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('classes', ['name' => 'JSS 4']);
    }

    public function test_updating_a_class_without_changing_its_name_does_not_trip_the_unique_check(): void
    {
        $class = SchoolClass::factory()->create(['name' => 'JSS 5']);

        $response = $this->actingAs($this->admin())->put("/classes/{$class->id}", [
            'name' => 'JSS 5', 'level' => 'junior_secondary', 'order' => 21,
        ]);

        $response->assertRedirect();
        $this->assertEquals(21, $class->fresh()->order);
    }

    public function test_administrator_can_create_a_subject(): void
    {
        $response = $this->actingAs($this->admin())->post('/subjects', [
            'name' => 'Further Mathematics', 'code' => 'FMATH', 'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subjects', ['code' => 'FMATH']);
    }

    public function test_updating_a_subject_without_changing_its_code_does_not_trip_the_unique_check(): void
    {
        $subject = Subject::factory()->create(['code' => 'CHEM']);

        $response = $this->actingAs($this->admin())->put("/subjects/{$subject->id}", [
            'name' => 'Chemistry', 'code' => 'CHEM', 'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Chemistry', $subject->fresh()->name);
    }

    public function test_administrator_can_create_an_academic_session(): void
    {
        $response = $this->actingAs($this->admin())->post('/academic-sessions', [
            'name' => '2099/2100', 'start_date' => '2099-09-01', 'end_date' => '2100-07-31',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('academic_sessions', ['name' => '2099/2100']);
    }

    public function test_updating_an_academic_session_without_changing_its_name_does_not_trip_the_unique_check(): void
    {
        $session = AcademicSession::factory()->create(['name' => '2050/2051']);

        $response = $this->actingAs($this->admin())->put("/academic-sessions/{$session->id}", [
            'name' => '2050/2051', 'start_date' => '2050-09-01', 'end_date' => '2051-07-31',
        ]);

        $response->assertRedirect();
    }

    public function test_administrator_can_create_a_fee_category(): void
    {
        $response = $this->actingAs($this->admin())->post('/fee-categories', [
            'name' => 'Sports Levy', 'code' => 'SPORT', 'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fee_categories', ['code' => 'SPORT']);
    }

    public function test_updating_a_fee_category_without_changing_its_code_does_not_trip_the_unique_check(): void
    {
        $category = FeeCategory::create(['name' => 'Old Name', 'code' => 'FCX', 'status' => 'active']);

        $response = $this->actingAs($this->admin())->put("/fee-categories/{$category->id}", [
            'name' => 'New Name', 'code' => 'FCX', 'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertEquals('New Name', $category->fresh()->name);
    }
}
