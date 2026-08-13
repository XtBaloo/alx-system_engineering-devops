<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\AssessmentScore;
use App\Models\AssessmentType;
use App\Models\ClassArm;
use App\Models\Result;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExaminationModuleTest extends TestCase
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

    protected function makeTeacherUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('teacher');
        Teacher::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    // -- Assessment Type CRUD -------------------------------------------------

    public function test_administrator_can_create_an_assessment_type(): void
    {
        $response = $this->actingAs($this->admin())->post('/assessment-types', [
            'name' => 'Class Test', 'code' => 'CT', 'max_score' => 20, 'order' => 1, 'is_active' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assessment_types', ['code' => 'CT', 'name' => 'Class Test']);
    }

    public function test_creating_an_assessment_type_with_a_duplicate_code_is_rejected(): void
    {
        AssessmentType::create(['name' => 'Assignment', 'code' => 'ASN', 'max_score' => 10, 'order' => 1]);

        $response = $this->actingAs($this->admin())->post('/assessment-types', [
            'name' => 'Another Assignment', 'code' => 'ASN', 'max_score' => 15, 'order' => 2,
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertEquals(1, AssessmentType::where('code', 'ASN')->count());
    }

    public function test_updating_an_assessment_type_without_changing_its_code_does_not_trip_the_unique_check(): void
    {
        $type = AssessmentType::create(['name' => 'CA', 'code' => 'CA', 'max_score' => 20, 'order' => 1]);

        $response = $this->actingAs($this->admin())->put("/assessment-types/{$type->id}", [
            'name' => 'Continuous Assessment', 'code' => 'CA', 'max_score' => 25, 'order' => 1,
        ]);

        $response->assertRedirect();
        $this->assertEquals('Continuous Assessment', $type->fresh()->name);
        $this->assertEquals(25, $type->fresh()->max_score);
    }

    public function test_updating_an_assessment_type_to_another_types_code_is_rejected(): void
    {
        AssessmentType::create(['name' => 'CA', 'code' => 'CA', 'max_score' => 20, 'order' => 1]);
        $exam = AssessmentType::create(['name' => 'Exam Prep', 'code' => 'EXP', 'max_score' => 20, 'order' => 2]);

        $response = $this->actingAs($this->admin())->put("/assessment-types/{$exam->id}", [
            'name' => 'Exam Prep', 'code' => 'CA', 'max_score' => 20, 'order' => 2,
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertEquals('EXP', $exam->fresh()->code);
    }

    public function test_assessment_types_are_listed_on_the_index_page(): void
    {
        AssessmentType::create(['name' => 'CA', 'code' => 'CA', 'max_score' => 20, 'order' => 1]);

        $response = $this->actingAs($this->admin())->get('/assessment-types');

        $response->assertOk();
        $response->assertSee('CA');
    }

    public function test_administrator_can_delete_an_assessment_type_without_scores(): void
    {
        $type = AssessmentType::create(['name' => 'CA', 'code' => 'CA', 'max_score' => 20, 'order' => 1]);

        $response = $this->actingAs($this->admin())->delete("/assessment-types/{$type->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('assessment_types', ['id' => $type->id]);
    }

    public function test_assessment_type_with_recorded_scores_cannot_be_deleted(): void
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

        $response = $this->actingAs($this->admin())->delete("/assessment-types/{$type->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('assessment_types', ['id' => $type->id]);
    }

    // -- Score Entry -----------------------------------------------------------

    protected function setUpScoreEntryFixtures(): array
    {
        $session = AcademicSession::factory()->active()->create();
        $term = Term::factory()->active()->create(['academic_session_id' => $session->id]);
        $classArm = ClassArm::factory()->create();
        $subject = Subject::factory()->create();
        $student = Student::factory()->create(['current_class_arm_id' => $classArm->id]);
        $type = AssessmentType::create(['name' => 'CA', 'code' => 'CA', 'max_score' => 20, 'order' => 1]);

        return compact('session', 'term', 'classArm', 'subject', 'student', 'type');
    }

    public function test_administrator_can_save_valid_scores_and_a_result_is_calculated(): void
    {
        ['classArm' => $classArm, 'subject' => $subject, 'student' => $student, 'type' => $type] = $this->setUpScoreEntryFixtures();

        $response = $this->actingAs($this->admin())->post('/scores', [
            'class_arm_id' => $classArm->id,
            'subject_id' => $subject->id,
            'assessments' => [$student->id => [$type->id => 18]],
            'examinations' => [$student->id => 55],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('assessment_scores', [
            'student_id' => $student->id, 'assessment_type_id' => $type->id, 'score' => 18,
        ]);
        $this->assertDatabaseHas('examination_scores', [
            'student_id' => $student->id, 'subject_id' => $subject->id, 'score' => 55,
        ]);

        $result = Result::where('student_id', $student->id)->where('subject_id', $subject->id)->first();
        $this->assertNotNull($result);
        $this->assertEquals(73, (float) $result->total_score);
    }

    public function test_a_score_above_the_assessment_types_max_score_is_rejected(): void
    {
        ['classArm' => $classArm, 'subject' => $subject, 'student' => $student, 'type' => $type] = $this->setUpScoreEntryFixtures();

        $response = $this->actingAs($this->admin())->post('/scores', [
            'class_arm_id' => $classArm->id,
            'subject_id' => $subject->id,
            'assessments' => [$student->id => [$type->id => 25]],
            'examinations' => [],
        ]);

        $response->assertSessionHasErrors('assessments.'.$student->id.'.'.$type->id);
        $this->assertDatabaseMissing('assessment_scores', ['student_id' => $student->id, 'assessment_type_id' => $type->id]);
    }

    public function test_a_score_above_the_configured_examination_max_score_is_rejected(): void
    {
        ['classArm' => $classArm, 'subject' => $subject, 'student' => $student] = $this->setUpScoreEntryFixtures();
        $maxScore = SchoolSetting::current()->examination_max_score;

        $response = $this->actingAs($this->admin())->post('/scores', [
            'class_arm_id' => $classArm->id,
            'subject_id' => $subject->id,
            'assessments' => [],
            'examinations' => [$student->id => $maxScore + 5],
        ]);

        $response->assertSessionHasErrors('examinations.'.$student->id);
        $this->assertDatabaseMissing('examination_scores', ['student_id' => $student->id]);
    }

    public function test_editing_an_existing_score_updates_it_instead_of_duplicating(): void
    {
        ['classArm' => $classArm, 'subject' => $subject, 'student' => $student, 'type' => $type, 'session' => $session, 'term' => $term] = $this->setUpScoreEntryFixtures();

        AssessmentScore::create([
            'student_id' => $student->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id, 'assessment_type_id' => $type->id, 'score' => 10,
        ]);

        $response = $this->actingAs($this->admin())->post('/scores', [
            'class_arm_id' => $classArm->id,
            'subject_id' => $subject->id,
            'assessments' => [$student->id => [$type->id => 16]],
            'examinations' => [],
        ]);

        $response->assertRedirect();
        $this->assertEquals(1, AssessmentScore::where('student_id', $student->id)->where('assessment_type_id', $type->id)->count());
        $this->assertDatabaseHas('assessment_scores', ['student_id' => $student->id, 'assessment_type_id' => $type->id, 'score' => 16]);
    }

    public function test_a_teacher_without_an_assignment_for_the_class_and_subject_cannot_enter_scores(): void
    {
        ['classArm' => $classArm, 'subject' => $subject, 'student' => $student, 'type' => $type] = $this->setUpScoreEntryFixtures();
        $teacherUser = $this->makeTeacherUser();

        $response = $this->actingAs($teacherUser)->post('/scores', [
            'class_arm_id' => $classArm->id,
            'subject_id' => $subject->id,
            'assessments' => [$student->id => [$type->id => 15]],
            'examinations' => [],
        ]);

        $response->assertForbidden();
    }

    public function test_a_teacher_with_a_matching_assignment_can_enter_scores(): void
    {
        ['classArm' => $classArm, 'subject' => $subject, 'student' => $student, 'type' => $type, 'session' => $session] = $this->setUpScoreEntryFixtures();
        $teacherUser = $this->makeTeacherUser();

        TeacherAssignment::create([
            'teacher_id' => $teacherUser->teacher->id,
            'class_arm_id' => $classArm->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
        ]);

        $response = $this->actingAs($teacherUser)->post('/scores', [
            'class_arm_id' => $classArm->id,
            'subject_id' => $subject->id,
            'assessments' => [$student->id => [$type->id => 15]],
            'examinations' => [],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assessment_scores', ['student_id' => $student->id, 'assessment_type_id' => $type->id, 'score' => 15]);
    }
}
