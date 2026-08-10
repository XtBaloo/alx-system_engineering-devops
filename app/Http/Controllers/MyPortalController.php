<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class MyPortalController extends Controller
{
    public function results(Request $request)
    {
        $student = $this->resolveStudent($request);
        abort_unless($student, 404);

        $results = $student->results()->where('status', 'published')
            ->with(['subject', 'term'])
            ->latest('id')->get()
            ->groupBy(fn ($r) => $r->term?->name);

        return view('my.results', compact('student', 'results'));
    }

    public function attendance(Request $request)
    {
        $student = $this->resolveStudent($request);
        abort_unless($student, 404);

        $attendances = $student->attendances()->with('term')->latest('date')->paginate(30);
        $summary = $student->attendances()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('my.attendance', compact('student', 'attendances', 'summary'));
    }

    public function fees(Request $request)
    {
        $student = $this->resolveStudent($request);
        abort_unless($student, 404);

        $fees = $student->studentFees()->with(['feeStructure.feeCategory', 'term', 'payments'])->latest('id')->get();

        return view('my.fees', compact('student', 'fees'));
    }

    public function switchChild(Request $request, Student $student)
    {
        $guardian = $request->user()->guardianProfile;
        abort_unless($guardian && $guardian->students()->where('students.id', $student->id)->exists(), 403);

        return redirect()->route('dashboard', ['child' => $student->id]);
    }

    protected function resolveStudent(Request $request): ?Student
    {
        $user = $request->user();

        if ($user->hasRole('student')) {
            return $user->student;
        }

        if ($user->hasRole('parent')) {
            $guardian = $user->guardianProfile;
            $children = $guardian ? $guardian->students : collect();
            $childId = $request->input('child', $children->first()?->id);

            return $children->firstWhere('id', (int) $childId);
        }

        return null;
    }
}
