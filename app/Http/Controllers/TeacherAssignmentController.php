<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\ClassArm;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;

class TeacherAssignmentController extends Controller
{
    public function index()
    {
        $assignments = TeacherAssignment::with(['teacher', 'classArm.schoolClass', 'subject', 'academicSession'])
            ->latest()->paginate(25);

        return view('teacher-assignments.index', compact('assignments'));
    }

    public function create()
    {
        $teachers = Teacher::active()->orderBy('first_name')->get();
        $classArms = ClassArm::with('schoolClass')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $sessions = AcademicSession::orderByDesc('start_date')->get();
        $currentSessionId = SchoolSetting::current()->current_academic_session_id;

        return view('teacher-assignments.create', compact('teachers', 'classArms', 'subjects', 'sessions', 'currentSessionId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'class_arm_id' => ['required', 'exists:class_arms,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
        ]);

        $exists = TeacherAssignment::where($data)->exists();

        if ($exists) {
            return back()->with('error', 'This teacher is already assigned to that class and subject for the session.')->withInput();
        }

        TeacherAssignment::create($data);

        return redirect()->route('teacher-assignments.index')->with('success', 'Assignment created.');
    }

    public function destroy(TeacherAssignment $teacherAssignment)
    {
        $teacherAssignment->delete();

        return back()->with('success', 'Assignment removed.');
    }
}
