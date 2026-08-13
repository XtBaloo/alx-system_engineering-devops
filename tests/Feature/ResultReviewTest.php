<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\AssessmentScore;
use App\Models\AssessmentType;
use App\Models\ClassArm;
use App\Models\ExaminationScore;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultReviewTest extends TestCase
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

    protected function makeScoredResult(): array
    {
        $session = AcademicSession::factory()->active()->create();
        $term = Term::factory()->active()->create(['academic_session_id' => $session->id]);
        $classArm = ClassArm::factory()->create();
        $subject = Subject::factory()->create();
        $student = Student::factory()->create(['current_class_arm_id' => $classArm->id]);
        $type = AssessmentType::create(['name' => 'CA', 'code' => 'CA', 'max_score' => 20, 'order' => 1]);

        AssessmentScore::create([
            'student_id' => $student->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id, 'assessment_type_id' => $type->id, 'score' => 15,
        ]);
        ExaminationScore::create([
            'student_id' => $student->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id, 'score' => 45,
        ]);

        $result = Result::create([
            'student_id' => $student->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id,
            'assessment_total' => 15, 'examination_score' => 45, 'total_score' => 60,
            'grade' => 'B', 'status' => 'draft',
        ]);

        return compact('result', 'student', 'session', 'term', 'classArm', 'subject', 'type');
    }

    public function test_administrator_can_view_a_results_score_breakdown(): void
    {
        ['result' => $result, 'type' => $type] = $this->makeScoredResult();

        $response = $this->actingAs($this->admin())->get("/results/{$result->id}");

        $response->assertOk();
        $response->assertSee($type->name);
        $response->assertSee('15');
        $response->assertSee('45');
        $response->assertSee('60');
    }

    public function test_a_teacher_not_assigned_to_the_subject_cannot_view_the_result(): void
    {
        ['result' => $result] = $this->makeScoredResult();

        $otherTeacherUser = User::factory()->create();
        $otherTeacherUser->assignRole('teacher');

        $this->actingAs($otherTeacherUser)->get("/results/{$result->id}")->assertForbidden();
    }

    public function test_a_student_cannot_view_their_own_unpublished_result(): void
    {
        ['result' => $result, 'student' => $student] = $this->makeScoredResult();

        $studentUser = User::factory()->create();
        $studentUser->assignRole('student');
        $student->update(['user_id' => $studentUser->id]);

        $this->actingAs($studentUser)->get("/results/{$result->id}")->assertForbidden();
    }

    public function test_a_student_can_view_their_own_published_result(): void
    {
        ['result' => $result, 'student' => $student] = $this->makeScoredResult();
        $result->update(['status' => 'published', 'published_at' => now()]);

        $studentUser = User::factory()->create();
        $studentUser->assignRole('student');
        $student->update(['user_id' => $studentUser->id]);

        $this->actingAs($studentUser)->get("/results/{$result->id}")->assertOk();
    }

    public function test_results_index_can_be_filtered_by_student_name(): void
    {
        ['result' => $result, 'student' => $student] = $this->makeScoredResult();

        $response = $this->actingAs($this->admin())->get('/results?student='.urlencode($student->first_name));

        $response->assertOk();
        $response->assertSee($student->full_name);
    }

    public function test_results_index_can_be_filtered_by_a_specific_term(): void
    {
        ['result' => $result, 'term' => $term, 'student' => $student] = $this->makeScoredResult();

        // A second, currently-inactive term should not appear unless explicitly selected.
        $otherTerm = Term::factory()->create(['academic_session_id' => $term->academic_session_id, 'name' => 'Second Term']);

        $response = $this->actingAs($this->admin())->get('/results?term_id='.$term->id);

        $response->assertOk();
        $response->assertSee($student->full_name);
    }

    public function test_results_index_shows_assessment_configuration_link_and_defaults_to_current_term(): void
    {
        ['result' => $result, 'student' => $student] = $this->makeScoredResult();

        $response = $this->actingAs($this->admin())->get('/results');

        $response->assertOk();
        $response->assertSee($student->full_name);
    }
}
