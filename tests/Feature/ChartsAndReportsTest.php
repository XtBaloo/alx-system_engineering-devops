<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\ClassArm;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\GradingScale;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartsAndReportsTest extends TestCase
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

    public function test_admin_dashboard_renders_all_chart_data(): void
    {
        $response = $this->actingAs($this->admin())->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('attendanceTrendChart', fn ($chart) => count($chart['labels']) === 14 && count($chart['datasets']) === 4);
        $response->assertViewHas('classDistributionChart', fn ($chart) => array_key_exists('labels', $chart) && array_key_exists('datasets', $chart));
        $response->assertViewHas('gradeDistributionChart', fn ($chart) => array_key_exists('labels', $chart) && array_key_exists('datasets', $chart));
    }

    public function test_students_by_class_chart_reflects_real_enrollment(): void
    {
        $schoolClass = SchoolClass::factory()->create(['name' => 'JSS 2', 'order' => 1]);
        $classArm = ClassArm::factory()->create(['school_class_id' => $schoolClass->id]);
        Student::factory()->count(3)->create(['current_class_arm_id' => $classArm->id, 'status' => 'active']);
        Student::factory()->create(['current_class_arm_id' => $classArm->id, 'status' => 'withdrawn']);

        $response = $this->actingAs($this->admin())->get('/dashboard');

        $response->assertViewHas('classDistributionChart', function ($chart) use ($schoolClass) {
            $index = array_search($schoolClass->name, $chart['labels']);

            return $index !== false && $chart['datasets'][0]['data'][$index] === 3;
        });
    }

    public function test_grade_distribution_chart_counts_only_published_results(): void
    {
        GradingScale::create(['min_score' => 70, 'max_score' => 100, 'grade' => 'A', 'remark' => 'Excellent', 'order' => 1]);
        GradingScale::create(['min_score' => 0, 'max_score' => 69, 'grade' => 'F', 'remark' => 'Fail', 'order' => 2]);

        $session = AcademicSession::factory()->active()->create();
        $term = Term::factory()->active()->create(['academic_session_id' => $session->id]);
        $classArm = ClassArm::factory()->create();
        $subject = Subject::factory()->create();

        Result::create([
            'student_id' => Student::factory()->create()->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id, 'grade' => 'A', 'status' => 'published',
        ]);
        Result::create([
            'student_id' => Student::factory()->create()->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id, 'grade' => 'A', 'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin())->get('/dashboard');

        $response->assertViewHas('gradeDistributionChart', function ($chart) {
            $index = array_search('A', $chart['labels']);

            return $chart['datasets'][0]['data'][$index] === 1;
        });
    }

    public function test_attendance_report_no_longer_errors_and_status_chart_matches_records(): void
    {
        $session = AcademicSession::factory()->active()->create();
        $term = Term::factory()->active()->create(['academic_session_id' => $session->id]);
        $classArm = ClassArm::factory()->create();
        $student = Student::factory()->create(['current_class_arm_id' => $classArm->id]);

        Attendance::create([
            'student_id' => $student->id, 'class_arm_id' => $classArm->id, 'academic_session_id' => $session->id,
            'term_id' => $term->id, 'date' => now(), 'status' => 'present',
        ]);
        Attendance::create([
            'student_id' => $student->id, 'class_arm_id' => $classArm->id, 'academic_session_id' => $session->id,
            'term_id' => $term->id, 'date' => now()->subDay(), 'status' => 'absent',
        ]);

        $response = $this->actingAs($this->admin())->get('/reports/attendance');

        $response->assertOk();
        $response->assertViewHas('statusChart', function ($chart) {
            $present = array_search('Present', $chart['labels']);
            $absent = array_search('Absent', $chart['labels']);

            return $chart['datasets'][0]['data'][$present] === 1 && $chart['datasets'][0]['data'][$absent] === 1;
        });
    }

    public function test_academic_report_grade_chart_respects_class_filter(): void
    {
        GradingScale::create(['min_score' => 0, 'max_score' => 100, 'grade' => 'A', 'remark' => 'Excellent', 'order' => 1]);

        $session = AcademicSession::factory()->create();
        $term = Term::factory()->create(['academic_session_id' => $session->id]);
        $subject = Subject::factory()->create();

        $classA = ClassArm::factory()->create();
        $classB = ClassArm::factory()->create();

        Result::create([
            'student_id' => Student::factory()->create()->id, 'subject_id' => $subject->id, 'class_arm_id' => $classA->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id, 'grade' => 'A', 'status' => 'published',
        ]);
        Result::create([
            'student_id' => Student::factory()->create()->id, 'subject_id' => $subject->id, 'class_arm_id' => $classB->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id, 'grade' => 'A', 'status' => 'published',
        ]);

        $response = $this->actingAs($this->admin())->get('/reports/academic?class_arm_id='.$classA->id);

        $response->assertOk();
        $response->assertViewHas('gradeChart', function ($chart) {
            $index = array_search('A', $chart['labels']);

            return $chart['datasets'][0]['data'][$index] === 1;
        });
    }

    public function test_finance_report_category_chart_matches_due_and_collected_totals(): void
    {
        $category = FeeCategory::create(['name' => 'Tuition', 'code' => 'TUITION', 'status' => 'active']);
        $session = AcademicSession::factory()->create();
        $term = Term::factory()->create(['academic_session_id' => $session->id]);
        $structure = FeeStructure::create([
            'fee_category_id' => $category->id, 'academic_session_id' => $session->id,
            'term_id' => $term->id, 'amount' => 50000, 'is_compulsory' => true,
        ]);
        StudentFee::create([
            'student_id' => Student::factory()->create()->id, 'fee_structure_id' => $structure->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id,
            'amount_due' => 50000, 'amount_paid' => 20000, 'status' => 'partial',
        ]);

        $response = $this->actingAs($this->admin())->get('/reports/finance');

        $response->assertOk();
        $response->assertViewHas('categoryChart', function ($chart) {
            $index = array_search('Tuition', $chart['labels']);

            return $chart['datasets'][0]['data'][$index] === 50000.0
                && $chart['datasets'][1]['data'][$index] === 20000.0;
        });
    }
}
