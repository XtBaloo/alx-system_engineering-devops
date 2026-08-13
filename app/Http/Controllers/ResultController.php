<?php

namespace App\Http\Controllers;

use App\Models\ClassArm;
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
        $term = $settings->currentTerm;

        $results = Result::with(['student', 'subject', 'classArm.schoolClass', 'term'])
            ->when($term, fn ($q) => $q->where('term_id', $term->id))
            ->when($request->class_arm_id, fn ($q, $id) => $q->where('class_arm_id', $id))
            ->when($request->subject_id, fn ($q, $id) => $q->where('subject_id', $id))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
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

        return view('results.index', compact('results', 'classArms', 'subjects', 'term'));
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

    public function submit(Result $result)
    {
        abort_unless((new ResultPolicy)->submit(auth()->user(), $result), 403);

        $result->update(['status' => 'submitted', 'submitted_by' => auth()->id(), 'submitted_at' => now()]);

        return back()->with('success', 'Result submitted for review.');
    }

    public function submitBatch(Request $request)
    {
        $data = $request->validate(['result_ids' => ['required', 'array'], 'result_ids.*' => ['exists:results,id']]);

        $policy = new ResultPolicy;
        $count = 0;

        foreach (Result::whereIn('id', $data['result_ids'])->get() as $result) {
            if ($policy->submit(auth()->user(), $result)) {
                $result->update(['status' => 'submitted', 'submitted_by' => auth()->id(), 'submitted_at' => now()]);
                $count++;
            }
        }

        return back()->with('success', "{$count} result(s) submitted for review.");
    }

    public function review(Result $result)
    {
        abort_unless((new ResultPolicy)->review(auth()->user(), $result), 403);

        $result->update(['status' => 'reviewed', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);

        return back()->with('success', 'Result marked as reviewed.');
    }

    public function approve(Result $result)
    {
        abort_unless((new ResultPolicy)->approve(auth()->user(), $result), 403);

        $result->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        return back()->with('success', 'Result approved.');
    }

    public function publish(Result $result, NotificationDispatcher $notifications)
    {
        abort_unless((new ResultPolicy)->publish(auth()->user(), $result), 403);

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
            if ($policy->publish(auth()->user(), $result)) {
                $result->update(['status' => 'published', 'published_by' => auth()->id(), 'published_at' => now()]);
                $notifications->resultPublished($result);
                $count++;
            }
        }

        return back()->with('success', "{$count} result(s) published.");
    }
}
