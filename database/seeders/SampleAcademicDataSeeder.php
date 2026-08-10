<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\AssessmentScore;
use App\Models\AssessmentType;
use App\Models\Attendance;
use App\Models\ClassArm;
use App\Models\ExaminationScore;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Services\ResultService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SampleAcademicDataSeeder extends Seeder
{
    public function run(): void
    {
        $jss1 = SchoolClass::where('name', 'JSS 1')->first();
        $jss1a = ClassArm::where('school_class_id', $jss1->id)->where('name', 'A')->first();
        $session = AcademicSession::where('is_active', true)->first();
        $term = Term::where('is_active', true)->first();
        $students = Student::where('current_class_arm_id', $jss1a->id)->get();

        // --- Attendance: last 10 school days ---------------------------------
        $date = Carbon::today();
        $daysAdded = 0;

        while ($daysAdded < 10) {
            $date = $date->copy()->subDay();

            if ($date->isWeekend()) {
                continue;
            }

            foreach ($students as $student) {
                Attendance::firstOrCreate(
                    ['student_id' => $student->id, 'date' => $date->format('Y-m-d')],
                    [
                        'class_arm_id' => $jss1a->id,
                        'academic_session_id' => $session->id,
                        'term_id' => $term->id,
                        'status' => fake()->randomElement(['present', 'present', 'present', 'present', 'late', 'absent']),
                    ]
                );
            }

            $daysAdded++;
        }

        // --- Scores + results for the three assigned subjects -----------------
        $subjects = Subject::whereIn('code', ['ENG', 'MTH', 'BSC'])->get();
        $assessmentTypes = AssessmentType::active()->get();
        $resultService = app(ResultService::class);

        foreach ($subjects as $subject) {
            foreach ($students as $student) {
                foreach ($assessmentTypes as $type) {
                    AssessmentScore::firstOrCreate([
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'term_id' => $term->id,
                        'assessment_type_id' => $type->id,
                    ], [
                        'class_arm_id' => $jss1a->id,
                        'academic_session_id' => $session->id,
                        'score' => fake()->numberBetween((int) ($type->max_score * 0.5), $type->max_score),
                    ]);
                }

                ExaminationScore::firstOrCreate([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'term_id' => $term->id,
                ], [
                    'class_arm_id' => $jss1a->id,
                    'academic_session_id' => $session->id,
                    'score' => fake()->numberBetween(30, 58),
                ]);

                $resultService->recalculateResult($student->id, $subject->id, $jss1a->id, $session->id, $term->id);
            }

            $resultService->recalculatePositions($jss1a->id, $subject->id, $term->id);
        }

        // Publish the demo student's results so the dashboards/report card have data
        $demoStudent = Student::where('admission_number', 'PFA/26/0001')->first();

        Result::where('student_id', $demoStudent->id)->where('term_id', $term->id)->get()->each(function (Result $result) {
            $result->update([
                'status' => 'published',
                'submitted_by' => null,
                'submitted_at' => now(),
                'reviewed_at' => now(),
                'approved_at' => now(),
                'published_at' => now(),
                'teacher_comment' => 'A pleasure to teach. Keep up the excellent effort.',
                'principal_comment' => 'A commendable result this term. Well done.',
            ]);
        });
    }
}
