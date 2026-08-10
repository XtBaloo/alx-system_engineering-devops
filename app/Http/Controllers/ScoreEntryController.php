<?php

namespace App\Http\Controllers;

use App\Models\AssessmentScore;
use App\Models\AssessmentType;
use App\Models\ClassArm;
use App\Models\ExaminationScore;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Services\ResultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScoreEntryController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();
        $settings = SchoolSetting::current();
        $session = $settings->currentAcademicSession;
        $term = $settings->currentTerm;

        $isManager = $user->can('manage-students');
        $assignments = $this->allowedAssignments($user, $session?->id);

        $classArms = $isManager
            ? ClassArm::with('schoolClass')->get()
            : $assignments->pluck('classArm')->filter()->unique('id')->values();

        $classArmId = $request->input('class_arm_id', $classArms->first()?->id);

        $subjects = ! $classArmId
            ? collect()
            : ($isManager
                ? ClassArm::find($classArmId)?->schoolClass?->subjects()->active()->get() ?? collect()
                : $assignments->where('class_arm_id', $classArmId)->pluck('subject')->filter()->unique('id')->values());

        $subjectId = $request->input('subject_id', $subjects->first()?->id);

        $authorized = ! $classArmId || ! $subjectId || $user->can('manage-students')
            || $assignments->where('class_arm_id', $classArmId)->where('subject_id', $subjectId)->isNotEmpty();

        abort_unless($authorized, 403);

        $students = ($classArmId)
            ? Student::active()->where('current_class_arm_id', $classArmId)->orderBy('first_name')->get()
            : collect();

        $assessmentTypes = AssessmentType::active()->get();

        $existingAssessments = collect();
        $existingExams = collect();

        if ($classArmId && $subjectId && $term) {
            $existingAssessments = AssessmentScore::where('subject_id', $subjectId)->where('term_id', $term->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()->groupBy(['student_id', 'assessment_type_id']);

            $existingExams = ExaminationScore::where('subject_id', $subjectId)->where('term_id', $term->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()->keyBy('student_id');
        }

        return view('scores.create', compact(
            'classArms', 'classArmId', 'subjects', 'subjectId', 'students',
            'assessmentTypes', 'existingAssessments', 'existingExams', 'session', 'term'
        ));
    }

    public function store(Request $request, ResultService $resultService)
    {
        $data = $request->validate([
            'class_arm_id' => ['required', 'exists:class_arms,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'assessments' => ['array'],
            'examinations' => ['array'],
        ]);

        $user = $request->user();
        $classArm = ClassArm::findOrFail($data['class_arm_id']);
        $settings = SchoolSetting::current();
        $session = $settings->currentAcademicSession;
        $term = $settings->currentTerm;

        if (! $session || ! $term) {
            return back()->with('error', 'Please set an active academic session and term first.');
        }

        if (! $term->isOpen()) {
            return back()->with('error', 'The current term is closed. Scores cannot be entered.');
        }

        $assignments = $this->allowedAssignments($user, $session->id);
        $authorized = $user->can('manage-students')
            || $assignments->where('class_arm_id', $classArm->id)->where('subject_id', $data['subject_id'])->isNotEmpty();

        abort_unless($authorized, 403);

        DB::transaction(function () use ($data, $classArm, $session, $term, $resultService) {
            $studentIds = collect(array_keys($data['assessments'] ?? []))
                ->merge(array_keys($data['examinations'] ?? []))
                ->unique();

            foreach ($studentIds as $studentId) {
                foreach ($data['assessments'][$studentId] ?? [] as $assessmentTypeId => $score) {
                    if ($score === null || $score === '') {
                        continue;
                    }

                    AssessmentScore::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'subject_id' => $data['subject_id'],
                            'term_id' => $term->id,
                            'assessment_type_id' => $assessmentTypeId,
                        ],
                        [
                            'class_arm_id' => $classArm->id,
                            'academic_session_id' => $session->id,
                            'score' => $score,
                            'entered_by' => auth()->id(),
                        ]
                    );
                }

                $examScore = $data['examinations'][$studentId] ?? null;

                if ($examScore !== null && $examScore !== '') {
                    ExaminationScore::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'subject_id' => $data['subject_id'],
                            'term_id' => $term->id,
                        ],
                        [
                            'class_arm_id' => $classArm->id,
                            'academic_session_id' => $session->id,
                            'score' => $examScore,
                            'entered_by' => auth()->id(),
                        ]
                    );
                }

                $resultService->recalculateResult((int) $studentId, (int) $data['subject_id'], $classArm->id, $session->id, $term->id);
            }

            $resultService->recalculatePositions($classArm->id, (int) $data['subject_id'], $term->id);
        });

        return back()->with('success', 'Scores saved successfully.');
    }

    protected function allowedAssignments($user, ?int $sessionId)
    {
        $teacherId = $user->teacher?->id;

        if (! $teacherId) {
            return collect();
        }

        return TeacherAssignment::with(['classArm.schoolClass', 'subject'])
            ->where('teacher_id', $teacherId)
            ->when($sessionId, fn ($q) => $q->where('academic_session_id', $sessionId))
            ->get();
    }
}
