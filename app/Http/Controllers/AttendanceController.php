<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassArm;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();
        $settings = SchoolSetting::current();
        $session = $settings->currentAcademicSession;
        $term = $settings->currentTerm;

        $classArms = $this->allowedClassArms($user);

        $classArmId = $request->input('class_arm_id', $classArms->first()?->id);
        $date = $request->input('date', now()->format('Y-m-d'));

        $classArm = $classArmId ? ClassArm::with('schoolClass')->find($classArmId) : null;

        if ($classArm) {
            abort_unless((new \App\Policies\AttendancePolicy)->take($user, $classArm), 403);
        }

        $students = $classArm
            ? Student::active()->where('current_class_arm_id', $classArm->id)->orderBy('first_name')->get()
            : collect();

        $existing = $classArm
            ? Attendance::where('class_arm_id', $classArm->id)->whereDate('date', $date)->get()->keyBy('student_id')
            : collect();

        return view('attendance.create', compact('classArms', 'classArm', 'students', 'existing', 'date', 'session', 'term'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'class_arm_id' => ['required', 'exists:class_arms,id'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', 'in:present,absent,late,excused'],
        ]);

        $classArm = ClassArm::findOrFail($data['class_arm_id']);
        abort_unless((new \App\Policies\AttendancePolicy)->take($request->user(), $classArm), 403);

        $settings = SchoolSetting::current();
        $session = $settings->currentAcademicSession;
        $term = $settings->currentTerm;

        if (! $session || ! $term) {
            return back()->with('error', 'Please set an active academic session and term before taking attendance.');
        }

        if (! $term->isOpen()) {
            return back()->with('error', 'The current term is closed. Attendance cannot be recorded.');
        }

        DB::transaction(function () use ($data, $classArm, $session, $term) {
            foreach ($data['statuses'] as $studentId => $status) {
                Attendance::updateOrCreate(
                    ['student_id' => $studentId, 'date' => $data['date']],
                    [
                        'class_arm_id' => $classArm->id,
                        'academic_session_id' => $session->id,
                        'term_id' => $term->id,
                        'status' => $status,
                        'recorded_by' => auth()->id(),
                    ]
                );
            }
        });

        return back()->with('success', 'Attendance saved for '.\Illuminate\Support\Carbon::parse($data['date'])->format('d M Y').'.');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $classArms = $this->allowedClassArms($user);

        $records = Attendance::with(['student', 'classArm.schoolClass'])
            ->when($request->class_arm_id, fn ($q, $id) => $q->where('class_arm_id', $id))
            ->when($request->date, fn ($q, $date) => $q->whereDate('date', $date))
            ->when(! $request->class_arm_id && $classArms->isNotEmpty() && ! $user->can('view-attendance'), fn ($q) => $q->whereIn('class_arm_id', $classArms->pluck('id')))
            ->latest('date')
            ->paginate(30)->withQueryString();

        return view('attendance.index', compact('records', 'classArms'));
    }

    protected function allowedClassArms($user)
    {
        if ($user->can('manage-students')) {
            return ClassArm::with('schoolClass')->get();
        }

        $teacherId = $user->teacher?->id;

        if (! $teacherId) {
            return collect();
        }

        $armIds = TeacherAssignment::where('teacher_id', $teacherId)->pluck('class_arm_id')
            ->merge(ClassArm::where('class_teacher_id', $teacherId)->pluck('id'))
            ->unique();

        return ClassArm::with('schoolClass')->whereIn('id', $armIds)->get();
    }
}
