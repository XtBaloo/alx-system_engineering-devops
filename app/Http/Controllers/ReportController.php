<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassArm;
use App\Models\Payment;
use App\Models\Result;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function students(Request $request)
    {
        $classArms = ClassArm::with('schoolClass')->get();

        $students = Student::with('currentClassArm.schoolClass')
            ->when($request->class_arm_id, fn ($q, $id) => $q->where('current_class_arm_id', $id))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('first_name')->paginate(30)->withQueryString();

        return view('reports.students', compact('students', 'classArms'));
    }

    public function attendance(Request $request)
    {
        $classArms = ClassArm::with('schoolClass')->get();
        $settings = SchoolSetting::current();
        $term = $settings->currentTerm;

        $summary = Attendance::query()
            ->when($request->class_arm_id, fn ($q, $id) => $q->where('class_arm_id', $id))
            ->when($term, fn ($q) => $q->where('term_id', $term->id))
            ->when($request->from, fn ($q, $d) => $q->whereDate('date', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('date', '<=', $d))
            ->join('students', 'students.id', '=', 'attendance.student_id')
            ->selectRaw('attendance.student_id, students.first_name, students.last_name, students.admission_number,
                SUM(CASE WHEN attendance.status = "present" THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN attendance.status = "absent" THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN attendance.status = "late" THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN attendance.status = "excused" THEN 1 ELSE 0 END) as excused,
                COUNT(*) as total')
            ->groupBy('attendance.student_id', 'students.first_name', 'students.last_name', 'students.admission_number')
            ->orderBy('students.first_name')
            ->paginate(30)->withQueryString();

        return view('reports.attendance', compact('summary', 'classArms', 'term'));
    }

    public function academic(Request $request)
    {
        $classArms = ClassArm::with('schoolClass')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $settings = SchoolSetting::current();
        $term = $settings->currentTerm;

        $results = Result::with(['student', 'subject', 'classArm.schoolClass'])
            ->published()
            ->when($term, fn ($q) => $q->where('term_id', $term->id))
            ->when($request->class_arm_id, fn ($q, $id) => $q->where('class_arm_id', $id))
            ->when($request->subject_id, fn ($q, $id) => $q->where('subject_id', $id))
            ->orderByDesc('total_score')
            ->paginate(30)->withQueryString();

        return view('reports.academic', compact('results', 'classArms', 'subjects', 'term'));
    }

    public function finance(Request $request)
    {
        $settings = SchoolSetting::current();
        $term = $settings->currentTerm;

        $totalDue = StudentFee::when($term, fn ($q) => $q->where('term_id', $term->id))->sum('amount_due');
        $totalPaid = StudentFee::when($term, fn ($q) => $q->where('term_id', $term->id))->sum('amount_paid');
        $totalOutstanding = $totalDue - $totalPaid;

        $byCategory = StudentFee::with('feeStructure.feeCategory')
            ->when($term, fn ($q) => $q->where('term_id', $term->id))
            ->get()
            ->groupBy(fn ($f) => $f->feeStructure?->feeCategory?->name ?? 'Uncategorised')
            ->map(fn ($group) => ['due' => $group->sum('amount_due'), 'paid' => $group->sum('amount_paid')]);

        $recentPayments = Payment::with('studentFee.student')
            ->when($request->from, fn ($q, $d) => $q->whereDate('payment_date', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('payment_date', '<=', $d))
            ->latest('payment_date')->paginate(30)->withQueryString();

        return view('reports.finance', compact('totalDue', 'totalPaid', 'totalOutstanding', 'byCategory', 'recentPayments', 'term'));
    }

    public function teachers()
    {
        $teachers = Teacher::withCount('teacherAssignments')->orderBy('first_name')->paginate(30);

        return view('reports.teachers', compact('teachers'));
    }
}
