<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassArm;
use App\Models\Guardian;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultPublicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function makeResult(): array
    {
        $session = AcademicSession::factory()->create();
        $term = Term::factory()->create(['academic_session_id' => $session->id]);
        $classArm = ClassArm::factory()->create();
        $subject = Subject::factory()->create();
        $student = Student::factory()->create(['current_class_arm_id' => $classArm->id]);

        $teacherUser = User::factory()->create();
        $teacherUser->assignRole('teacher');
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);

        TeacherAssignment::create([
            'teacher_id' => $teacher->id, 'class_arm_id' => $classArm->id,
            'subject_id' => $subject->id, 'academic_session_id' => $session->id,
        ]);

        $result = Result::create([
            'student_id' => $student->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id,
            'assessment_total' => 30, 'examination_score' => 50, 'total_score' => 80,
            'grade' => 'A', 'status' => 'draft',
        ]);

        return compact('result', 'teacherUser', 'student');
    }

    public function test_a_result_moves_through_the_full_workflow_to_publication(): void
    {
        ['result' => $result, 'teacherUser' => $teacherUser] = $this->makeResult();

        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $this->actingAs($teacherUser)->post("/results/{$result->id}/submit")->assertRedirect();
        $this->assertEquals('submitted', $result->fresh()->status);

        $this->actingAs($admin)->post("/results/{$result->id}/review")->assertRedirect();
        $this->assertEquals('reviewed', $result->fresh()->status);

        $this->actingAs($admin)->post("/results/{$result->id}/approve")->assertRedirect();
        $this->assertEquals('approved', $result->fresh()->status);

        $this->actingAs($admin)->post("/results/{$result->id}/publish")->assertRedirect();
        $this->assertEquals('published', $result->fresh()->status);
        $this->assertNotNull($result->fresh()->published_at);
    }

    public function test_only_the_assigned_teacher_can_submit_a_result(): void
    {
        ['result' => $result] = $this->makeResult();

        $otherTeacherUser = User::factory()->create();
        $otherTeacherUser->assignRole('teacher');

        $response = $this->actingAs($otherTeacherUser)->post("/results/{$result->id}/submit");

        $response->assertForbidden();
        $this->assertEquals('draft', $result->fresh()->status);
    }

    public function test_a_result_must_be_reviewed_before_it_can_be_approved(): void
    {
        ['result' => $result] = $this->makeResult();
        $result->update(['status' => 'submitted']);

        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Directly hitting approve while still "submitted" (not yet reviewed) must be rejected.
        $response = $this->actingAs($admin)->post("/results/{$result->id}/approve");
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals('submitted', $result->fresh()->status);

        $this->actingAs($admin)->post("/results/{$result->id}/review")->assertRedirect();
        $this->assertEquals('reviewed', $result->fresh()->status);

        $this->actingAs($admin)->post("/results/{$result->id}/approve")->assertRedirect();
        $this->assertEquals('approved', $result->fresh()->status);
    }

    public function test_a_result_must_be_approved_before_it_can_be_published(): void
    {
        ['result' => $result] = $this->makeResult();
        $result->update(['status' => 'reviewed']);

        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->post("/results/{$result->id}/publish");
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals('reviewed', $result->fresh()->status);
    }

    public function test_a_non_draft_result_cannot_be_resubmitted(): void
    {
        ['result' => $result, 'teacherUser' => $teacherUser] = $this->makeResult();
        $result->update(['status' => 'approved']);

        $response = $this->actingAs($teacherUser)->post("/results/{$result->id}/submit");
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals('approved', $result->fresh()->status);
    }

    public function test_an_already_published_result_cannot_be_published_again(): void
    {
        ['result' => $result] = $this->makeResult();
        $result->update(['status' => 'published', 'published_at' => now()]);

        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->post("/results/{$result->id}/publish");
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_batch_submit_only_advances_results_that_are_currently_in_draft_status(): void
    {
        $session = AcademicSession::factory()->create();
        $term = Term::factory()->create(['academic_session_id' => $session->id]);
        $classArm = ClassArm::factory()->create();
        $subject = Subject::factory()->create();

        $teacherUser = User::factory()->create();
        $teacherUser->assignRole('teacher');
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
        TeacherAssignment::create([
            'teacher_id' => $teacher->id, 'class_arm_id' => $classArm->id,
            'subject_id' => $subject->id, 'academic_session_id' => $session->id,
        ]);

        $draftStudent = Student::factory()->create(['current_class_arm_id' => $classArm->id]);
        $alreadySubmittedStudent = Student::factory()->create(['current_class_arm_id' => $classArm->id]);

        $draftResult = Result::create([
            'student_id' => $draftStudent->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id,
            'assessment_total' => 30, 'examination_score' => 50, 'total_score' => 80,
            'grade' => 'A', 'status' => 'draft',
        ]);
        $alreadySubmittedResult = Result::create([
            'student_id' => $alreadySubmittedStudent->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id,
            'assessment_total' => 20, 'examination_score' => 40, 'total_score' => 60,
            'grade' => 'C', 'status' => 'submitted', 'submitted_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($teacherUser)->post('/results/submit-batch', [
            'result_ids' => [$draftResult->id, $alreadySubmittedResult->id],
        ]);

        $response->assertRedirect();
        $this->assertEquals('submitted', $draftResult->fresh()->status);
        // Already-submitted result is left untouched (its submitted_at must not be bumped by a re-submit).
        $this->assertTrue($alreadySubmittedResult->fresh()->submitted_at->equalTo($alreadySubmittedResult->submitted_at));
    }

    public function test_unpublished_results_are_not_visible_to_students(): void
    {
        ['result' => $result, 'student' => $student] = $this->makeResult();

        $studentUser = User::factory()->create();
        $studentUser->assignRole('student');
        $student->update(['user_id' => $studentUser->id]);

        $response = $this->actingAs($studentUser)->get('/my/results');

        $response->assertOk();
        $response->assertDontSee($result->grade.' ');
    }

    public function test_published_results_are_visible_to_the_owning_student_and_their_guardian(): void
    {
        ['result' => $result, 'student' => $student] = $this->makeResult();
        $result->update(['status' => 'published', 'published_at' => now()]);

        $studentUser = User::factory()->create();
        $studentUser->assignRole('student');
        $student->update(['user_id' => $studentUser->id]);

        $this->actingAs($studentUser)->get('/my/results')->assertOk()->assertSee($result->subject->name);

        $parentUser = User::factory()->create();
        $parentUser->assignRole('parent');
        $guardian = Guardian::factory()->create(['user_id' => $parentUser->id]);
        $guardian->students()->attach($student->id, ['relationship' => 'Father', 'is_primary' => true]);

        $this->actingAs($parentUser)->get('/my/results')->assertOk()->assertSee($result->subject->name);
    }
}
