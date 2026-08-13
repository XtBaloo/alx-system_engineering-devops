<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\AssessmentScore;
use App\Models\AssessmentType;
use App\Models\ClassArm;
use App\Models\ExaminationScore;
use App\Models\Result;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Policies\ResultPolicy;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $settings = SchoolSetting::current();
        $term = $request->term_id ? \App\Models\Term::find($request->term_id) : $settings->currentTerm;

        $results = Result::with(['student', 'subject', 'classArm.schoolClass', 'term'])
            ->when($term, fn ($q) => $q->where('term_id', $term->id))
            ->when(! $term && $request->academic_session_id, fn ($q, $id) => $q->where('academic_session_id', $id))
            ->when($request->class_arm_id, fn ($q, $id) => $q->where('class_arm_id', $id))
            ->when($request->subject_id, fn ($q, $id) => $q->where('subject_id', $id))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->student, function ($q, $search) {
                $q->whereHas('student', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%");
                });
            })
            ->when(! $user->can('manage-students'), function ($q) use ($user) {
                $teacherId = $user->teacher?->id;
                $assignedArmSubjectPairs = \App\Models\TeacherAssignment::where('teacher_id', $teacherId)->get(['class_arm_id', 'subject_id']);

                $q->where(function ($q2) use ($assignedArmSubjectPairs) {
                    foreach ($assignedArmSubjectPairs as $pair) {
                        $q2->orWhere(fn ($q3) => $q3->where('class_arm_id', $pair->class_arm_id)->where('subject_id', $pair->subject_id));
                    }
                });
            })
            ->latest('id')
            ->paginate(30)->withQueryString();

        $classArms = ClassArm::with('schoolClass')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $sessions = AcademicSession::orderByDesc('start_date')->get();
        $terms = \App\Models\Term::with('academicSession')->orderByDesc('start_date')->get();

        return view('results.index', compact('results', 'classArms', 'subjects', 'sessions', 'terms', 'term'));
    }

    public function show(Request $request, Result $result)
    {
        abort_unless((new ResultPolicy)->view($request->user(), $result), 403);

        $result->load(['student', 'subject', 'classArm.schoolClass', 'academicSession', 'term', 'submittedBy', 'reviewedBy', 'approvedBy', 'publishedBy']);

        $scoreFilter = [
            'student_id' => $result->student_id,
            'subject_id' => $result->subject_id,
            'term_id' => $result->term_id,
        ];

        $assessmentTypes = AssessmentType::orderBy('order')->get();
        $assessmentScores = AssessmentScore::where($scoreFilter)->get()->keyBy('assessment_type_id');
        $examinationScore = ExaminationScore::where($scoreFilter)->first();
        $examMaxScore = SchoolSetting::current()->examination_max_score;

        return view('results.show', compact('result', 'assessmentTypes', 'assessmentScores', 'examinationScore', 'examMaxScore'));
    }

    public function published(Request $request)
    {
        $settings = SchoolSetting::current();
        $term = $settings->currentTerm;

        $results = Result::published()
            ->with(['student', 'subject', 'classArm.schoolClass'])
            ->when($term, fn ($q) => $q->where('term_id', $term->id))
            ->when($request->class_arm_id, fn ($q, $id) => $q->where('class_arm_id', $id))
            ->latest('published_at')
            ->paginate(30)->withQueryString();

        $classArms = ClassArm::with('schoolClass')->get();

        return view('results.published', compact('results', 'classArms', 'term'));
    }

    /**
     * The status a result must currently be in for each transition to be valid.
     * Keeps the workflow strictly sequential: draft -> submitted -> reviewed -> approved -> published.
     */
    protected const REQUIRED_STATUS = [
        'submit' => 'draft',
        'review' => 'submitted',
        'approve' => 'reviewed',
        'publish' => 'approved',
    ];

    protected function canTransition(Result $result, string $action): bool
    {
        return $result->status === self::REQUIRED_STATUS[$action];
    }

    public function submit(Result $result)
    {
        abort_unless((new ResultPolicy)->submit(auth()->user(), $result), 403);

        if (! $this->canTransition($result, 'submit')) {
            return back()->with('error', 'Only draft results can be submitted for review.');
        }

        $result->update(['status' => 'submitted', 'submitted_by' => auth()->id(), 'submitted_at' => now()]);

        return back()->with('success', 'Result submitted for review.');
    }

    public function submitBatch(Request $request)
    {
        $data = $request->validate(['result_ids' => ['required', 'array'], 'result_ids.*' => ['exists:results,id']]);

        $policy = new ResultPolicy;
        $count = 0;

        foreach (Result::whereIn('id', $data['result_ids'])->get() as $result) {
            if ($policy->submit(auth()->user(), $result) && $this->canTransition($result, 'submit')) {
                $result->update(['status' => 'submitted', 'submitted_by' => auth()->id(), 'submitted_at' => now()]);
                $count++;
            }
        }

        return $count > 0
            ? back()->with('success', "{$count} result(s) submitted for review.")
            : back()->with('error', 'No selected results were eligible to be submitted.');
    }

    public function review(Result $result)
    {
        abort_unless((new ResultPolicy)->review(auth()->user(), $result), 403);

        if (! $this->canTransition($result, 'review')) {
            return back()->with('error', 'Only submitted results can be marked as reviewed.');
        }

        $result->update(['status' => 'reviewed', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);

        return back()->with('success', 'Result marked as reviewed.');
    }

    public function approve(Result $result)
    {
        abort_unless((new ResultPolicy)->approve(auth()->user(), $result), 403);

        if (! $this->canTransition($result, 'approve')) {
            return back()->with('error', 'A result must be reviewed before it can be approved.');
        }

        $result->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        return back()->with('success', 'Result approved.');
    }

    public function publish(Result $result, NotificationDispatcher $notifications)
    {
        abort_unless((new ResultPolicy)->publish(auth()->user(), $result), 403);

        if (! $this->canTransition($result, 'publish')) {
            return back()->with('error', 'A result must be approved before it can be published.');
        }

        $result->update(['status' => 'published', 'published_by' => auth()->id(), 'published_at' => now()]);
        $notifications->resultPublished($result);

        return back()->with('success', 'Result published. Students and parents can now view it.');
    }

    public function publishBatch(Request $request, NotificationDispatcher $notifications)
    {
        $data = $request->validate(['result_ids' => ['required', 'array'], 'result_ids.*' => ['exists:results,id']]);

        $policy = new ResultPolicy;
        $count = 0;

        foreach (Result::whereIn('id', $data['result_ids'])->get() as $result) {
            if ($policy->publish(auth()->user(), $result) && $this->canTransition($result, 'publish')) {
                $result->update(['status' => 'published', 'published_by' => auth()->id(), 'published_at' => now()]);
                $notifications->resultPublished($result);
                $count++;
            }
        }

        return $count > 0
            ? back()->with('success', "{$count} result(s) published.")
            : back()->with('error', 'No selected results were eligible to be published.');
    }
}
