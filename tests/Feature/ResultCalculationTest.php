<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\AssessmentScore;
use App\Models\AssessmentType;
use App\Models\ClassArm;
use App\Models\ExaminationScore;
use App\Models\GradingScale;
use App\Models\Result;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Services\ResultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function seedGrading(): void
    {
        $grades = [
            ['min_score' => 70, 'max_score' => 100, 'grade' => 'A', 'remark' => 'Excellent', 'grade_point' => 5, 'order' => 1],
            ['min_score' => 60, 'max_score' => 69, 'grade' => 'B', 'remark' => 'Very Good', 'grade_point' => 4, 'order' => 2],
            ['min_score' => 50, 'max_score' => 59, 'grade' => 'C', 'remark' => 'Good', 'grade_point' => 3, 'order' => 3],
            ['min_score' => 45, 'max_score' => 49, 'grade' => 'D', 'remark' => 'Fair', 'grade_point' => 2, 'order' => 4],
            ['min_score' => 40, 'max_score' => 44, 'grade' => 'E', 'remark' => 'Pass', 'grade_point' => 1, 'order' => 5],
            ['min_score' => 0, 'max_score' => 39, 'grade' => 'F', 'remark' => 'Fail', 'grade_point' => 0, 'order' => 6],
        ];

        foreach ($grades as $g) {
            GradingScale::create($g);
        }
    }

    public function test_grading_scale_resolves_the_correct_grade_for_a_score(): void
    {
        $this->seedGrading();

        $this->assertEquals('A', GradingScale::forScore(75)->grade);
        $this->assertEquals('B', GradingScale::forScore(65)->grade);
        $this->assertEquals('F', GradingScale::forScore(10)->grade);
        $this->assertEquals('E', GradingScale::forScore(40)->grade);
    }

    public function test_result_service_sums_assessment_and_examination_scores_and_assigns_a_grade(): void
    {
        $this->seedGrading();

        $classArm = ClassArm::factory()->create();
        $session = AcademicSession::factory()->create();
        $term = Term::factory()->create(['academic_session_id' => $session->id]);
        $subject = Subject::factory()->create();
        $student = Student::factory()->create(['current_class_arm_id' => $classArm->id]);

        $ca1 = AssessmentType::create(['name' => 'CA 1', 'code' => 'CA1', 'max_score' => 20, 'order' => 1, 'is_active' => true]);
        $ca2 = AssessmentType::create(['name' => 'CA 2', 'code' => 'CA2', 'max_score' => 20, 'order' => 2, 'is_active' => true]);

        AssessmentScore::create([
            'student_id' => $student->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id, 'assessment_type_id' => $ca1->id, 'score' => 18,
        ]);
        AssessmentScore::create([
            'student_id' => $student->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id, 'assessment_type_id' => $ca2->id, 'score' => 17,
        ]);
        ExaminationScore::create([
            'student_id' => $student->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id, 'score' => 40,
        ]);

        $result = (new ResultService)->recalculateResult($student->id, $subject->id, $classArm->id, $session->id, $term->id);

        $this->assertEquals(35, (float) $result->assessment_total);
        $this->assertEquals(40, (float) $result->examination_score);
        $this->assertEquals(75, (float) $result->total_score);
        $this->assertEquals('A', $result->grade);
        $this->assertEquals('draft', $result->status);
    }

    public function test_no_result_row_is_created_when_a_student_has_no_scores_recorded_yet(): void
    {
        $this->seedGrading();

        $classArm = ClassArm::factory()->create();
        $session = AcademicSession::factory()->create();
        $term = Term::factory()->create(['academic_session_id' => $session->id]);
        $subject = Subject::factory()->create();
        $student = Student::factory()->create(['current_class_arm_id' => $classArm->id]);

        $result = (new ResultService)->recalculateResult($student->id, $subject->id, $classArm->id, $session->id, $term->id);

        $this->assertFalse($result->exists);
        $this->assertDatabaseMissing('results', [
            'student_id' => $student->id, 'subject_id' => $subject->id, 'term_id' => $term->id,
        ]);
    }

    public function test_a_result_is_created_once_at_least_one_score_component_is_recorded(): void
    {
        $this->seedGrading();

        $classArm = ClassArm::factory()->create();
        $session = AcademicSession::factory()->create();
        $term = Term::factory()->create(['academic_session_id' => $session->id]);
        $subject = Subject::factory()->create();
        $student = Student::factory()->create(['current_class_arm_id' => $classArm->id]);

        ExaminationScore::create([
            'student_id' => $student->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id, 'score' => 30,
        ]);

        $result = (new ResultService)->recalculateResult($student->id, $subject->id, $classArm->id, $session->id, $term->id);

        $this->assertTrue($result->exists);
        $this->assertEquals(30, (float) $result->total_score);
        $this->assertEquals('draft', $result->status);
    }

    public function test_published_results_are_protected_from_being_silently_recalculated(): void
    {
        $this->seedGrading();

        $classArm = ClassArm::factory()->create();
        $session = AcademicSession::factory()->create();
        $term = Term::factory()->create(['academic_session_id' => $session->id]);
        $subject = Subject::factory()->create();
        $student = Student::factory()->create(['current_class_arm_id' => $classArm->id]);

        $result = Result::create([
            'student_id' => $student->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id,
            'assessment_total' => 30, 'examination_score' => 50, 'total_score' => 80,
            'grade' => 'A', 'status' => 'published',
        ]);

        AssessmentScore::create([
            'student_id' => $student->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id,
            'assessment_type_id' => AssessmentType::create(['name' => 'CA', 'code' => 'CA', 'max_score' => 40, 'order' => 1])->id,
            'score' => 5,
        ]);

        $recalculated = (new ResultService)->recalculateResult($student->id, $subject->id, $classArm->id, $session->id, $term->id);

        $this->assertEquals(80, (float) $recalculated->total_score);
        $this->assertEquals('published', $recalculated->status);
    }

    public function test_class_positions_are_ranked_with_standard_competition_ranking_for_ties(): void
    {
        $this->seedGrading();
        SchoolSetting::current()->update(['ranking_method' => 'standard_competition']);

        $classArm = ClassArm::factory()->create();
        $session = AcademicSession::factory()->create();
        $term = Term::factory()->create(['academic_session_id' => $session->id]);
        $subject = Subject::factory()->create();

        $scores = [90, 90, 70, 60];
        $students = collect($scores)->map(fn ($score) => Student::factory()->create(['current_class_arm_id' => $classArm->id]));

        foreach ($students as $i => $student) {
            Result::create([
                'student_id' => $student->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
                'academic_session_id' => $session->id, 'term_id' => $term->id,
                'assessment_total' => 0, 'examination_score' => $scores[$i], 'total_score' => $scores[$i],
                'grade' => 'A', 'status' => 'draft',
            ]);
        }

        (new ResultService)->recalculatePositions($classArm->id, $subject->id, $term->id);

        $positions = Result::where('subject_id', $subject->id)->orderBy('total_score', 'desc')->pluck('position')->toArray();

        // Two students tied for first (90) both get position 1, next gets 3, then 4 -> 1,1,3,4
        $this->assertEquals([1, 1, 3, 4], $positions);
    }

    public function test_class_positions_use_dense_ranking_when_configured(): void
    {
        $this->seedGrading();
        SchoolSetting::current()->update(['ranking_method' => 'dense']);

        $classArm = ClassArm::factory()->create();
        $session = AcademicSession::factory()->create();
        $term = Term::factory()->create(['academic_session_id' => $session->id]);
        $subject = Subject::factory()->create();

        $scores = [90, 90, 70, 60];
        $students = collect($scores)->map(fn ($score) => Student::factory()->create(['current_class_arm_id' => $classArm->id]));

        foreach ($students as $i => $student) {
            Result::create([
                'student_id' => $student->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
                'academic_session_id' => $session->id, 'term_id' => $term->id,
                'assessment_total' => 0, 'examination_score' => $scores[$i], 'total_score' => $scores[$i],
                'grade' => 'A', 'status' => 'draft',
            ]);
        }

        (new ResultService)->recalculatePositions($classArm->id, $subject->id, $term->id);

        $positions = Result::where('subject_id', $subject->id)->orderBy('total_score', 'desc')->pluck('position')->toArray();

        // Dense ranking: tied students share position 1, next distinct score gets 2, then 3 -> 1,1,2,3
        $this->assertEquals([1, 1, 2, 3], $positions);
    }
}
