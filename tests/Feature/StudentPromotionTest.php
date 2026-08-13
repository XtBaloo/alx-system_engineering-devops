<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassArm;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_promoting_a_student_creates_a_new_enrollment_and_preserves_history(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        // Student was enrolled in a previous (now inactive) session...
        $previousSession = AcademicSession::factory()->create(['is_active' => false]);
        $jss1 = SchoolClass::factory()->create(['name' => 'JSS 1']);
        $jss1a = ClassArm::factory()->create(['school_class_id' => $jss1->id, 'name' => 'A']);

        $student = Student::factory()->create(['current_class_arm_id' => $jss1a->id]);
        $originalEnrollment = Enrollment::create([
            'student_id' => $student->id,
            'academic_session_id' => $previousSession->id,
            'school_class_id' => $jss1->id,
            'class_arm_id' => $jss1a->id,
            'status' => 'active',
        ]);

        // ...and is now being promoted into the newly active session's class.
        AcademicSession::factory()->active()->create();
        $jss2 = SchoolClass::factory()->create(['name' => 'JSS 2']);
        $jss2a = ClassArm::factory()->create(['school_class_id' => $jss2->id, 'name' => 'A']);

        $response = $this->actingAs($admin)->post('/students/promotions', [
            'target_class_arm_id' => $jss2a->id,
            'student_ids' => [$student->id],
        ]);

        $response->assertRedirect(route('students.promotions'));

        $student->refresh();
        $this->assertEquals($jss2a->id, $student->current_class_arm_id);

        $originalEnrollment->refresh();
        $this->assertEquals('promoted', $originalEnrollment->status);

        $newEnrollment = Enrollment::where('student_id', $student->id)->where('status', 'active')->first();
        $this->assertNotNull($newEnrollment);
        $this->assertEquals($jss2a->id, $newEnrollment->class_arm_id);
        $this->assertEquals($originalEnrollment->id, $newEnrollment->promoted_from_enrollment_id);

        // Historical enrollment record must never be destroyed.
        $this->assertDatabaseHas('enrollments', ['id' => $originalEnrollment->id, 'class_arm_id' => $jss1a->id]);
    }

    public function test_promoting_with_no_students_selected_is_rejected_with_a_field_error(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        AcademicSession::factory()->active()->create();
        $targetArm = ClassArm::factory()->create();

        $response = $this->actingAs($admin)->post('/students/promotions', [
            'target_class_arm_id' => $targetArm->id,
            'student_ids' => [],
        ]);

        $response->assertSessionHasErrors('student_ids');
    }

    public function test_promoting_without_a_target_class_is_rejected_and_shown_inline_on_the_form(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        AcademicSession::factory()->active()->create();
        $sourceArm = ClassArm::factory()->create();
        $student = Student::factory()->create(['current_class_arm_id' => $sourceArm->id]);

        $this->actingAs($admin)->post('/students/promotions', [
            'student_ids' => [$student->id],
        ]);

        $response = $this->actingAs($admin)->get('/students/promotions?source_class_arm_id='.$sourceArm->id);

        $response->assertOk();
        $response->assertSee('The target class arm id field is required.');
    }

    public function test_a_promotion_can_be_reversed_restoring_the_previous_class(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $previousSession = AcademicSession::factory()->create(['is_active' => false]);
        $jss1a = ClassArm::factory()->create();
        $jss2a = ClassArm::factory()->create();

        $student = Student::factory()->create(['current_class_arm_id' => $jss1a->id]);
        $originalEnrollment = Enrollment::create([
            'student_id' => $student->id,
            'academic_session_id' => $previousSession->id,
            'school_class_id' => $jss1a->school_class_id,
            'class_arm_id' => $jss1a->id,
            'status' => 'active',
        ]);

        AcademicSession::factory()->active()->create();

        $this->actingAs($admin)->post('/students/promotions', [
            'target_class_arm_id' => $jss2a->id,
            'student_ids' => [$student->id],
        ]);

        $newEnrollment = Enrollment::where('student_id', $student->id)->where('status', 'active')->first();
        $this->assertNotNull($newEnrollment);

        $response = $this->actingAs($admin)->post("/students/{$student->id}/reverse-promotion/{$newEnrollment->id}");
        $response->assertRedirect();

        $student->refresh();
        $this->assertEquals($jss1a->id, $student->current_class_arm_id);

        $originalEnrollment->refresh();
        $this->assertEquals('active', $originalEnrollment->status);

        $this->assertDatabaseMissing('enrollments', ['id' => $newEnrollment->id]);
    }
}
