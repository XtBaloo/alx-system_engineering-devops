<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassArm;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAssignmentTest extends TestCase
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

    public function test_administrator_can_create_a_teacher_assignment(): void
    {
        $teacher = Teacher::factory()->create();
        $classArm = ClassArm::factory()->create();
        $subject = Subject::factory()->create();
        $session = AcademicSession::factory()->create();

        $response = $this->actingAs($this->admin())->post('/teacher-assignments', [
            'teacher_id' => $teacher->id,
            'class_arm_id' => $classArm->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('teacher_assignments', [
            'teacher_id' => $teacher->id, 'class_arm_id' => $classArm->id,
            'subject_id' => $subject->id, 'academic_session_id' => $session->id,
        ]);
    }

    public function test_missing_required_fields_are_rejected_with_field_errors(): void
    {
        $response = $this->actingAs($this->admin())->post('/teacher-assignments', []);

        $response->assertSessionHasErrors(['teacher_id', 'class_arm_id', 'subject_id', 'academic_session_id']);
        $this->assertDatabaseCount('teacher_assignments', 0);
    }

    public function test_validation_errors_are_displayed_inline_on_the_create_page(): void
    {
        $this->actingAs($this->admin())->post('/teacher-assignments', []);

        $response = $this->actingAs($this->admin())->get('/teacher-assignments/create');

        $response->assertOk();
        $response->assertSee('The teacher id field is required.');
    }

    public function test_a_duplicate_assignment_is_rejected_with_a_flash_error_not_a_field_error(): void
    {
        $teacher = Teacher::factory()->create();
        $classArm = ClassArm::factory()->create();
        $subject = Subject::factory()->create();
        $session = AcademicSession::factory()->create();

        TeacherAssignment::create([
            'teacher_id' => $teacher->id, 'class_arm_id' => $classArm->id,
            'subject_id' => $subject->id, 'academic_session_id' => $session->id,
        ]);

        $response = $this->actingAs($this->admin())->post('/teacher-assignments', [
            'teacher_id' => $teacher->id,
            'class_arm_id' => $classArm->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('teacher_assignments', 1);
    }
}
