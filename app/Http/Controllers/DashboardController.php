<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasAnyRole(['super-admin', 'administrator'])) {
            return $this->adminDashboard();
        }

        if ($user->hasRole('teacher')) {
            return $this->teacherDashboard($user);
        }

        if ($user->hasRole('student')) {
            return $this->studentDashboard($user);
        }

        if ($user->hasRole('parent')) {
            return $this->parentDashboard($user);
        }

        abort(403, 'No dashboard is configured for your account.');
    }

    protected function adminDashboard()
    {
        $settings = SchoolSetting::current();
        $session = $settings->currentAcademicSession;
        $term = $settings->currentTerm;

        $today = Carbon::today();
        $presentToday = $term ? Attendance::where('term_id', $term->id)->whereDate('date', $today)->where('status', 'present')->count() : 0;
        $absentToday = $term ? Attendance::where('term_id', $term->id)->whereDate('date', $today)->where('status', 'absent')->count() : 0;

        $feesQuery = $term ? StudentFee::where('term_id', $term->id) : StudentFee::query();

        $stats = [
            'total_students' => Student::active()->count(),
            'total_teachers' => Teacher::active()->count(),
            'total_classes' => \App\Models\SchoolClass::count(),
            'present_today' => $presentToday,
            'absent_today' => $absentToday,
            'fees_collected' => (clone $feesQuery)->sum('amount_paid'),
            'fees_outstanding' => (clone $feesQuery)->outstanding()->get()->sum('balance'),
        ];

        $recentStudents = Student::latest()->take(5)->get();
        $recentPayments = Payment::with('studentFee.student')->latest()->take(5)->get();
        $recentAnnouncements = Announcement::published()->latest('published_at')->take(5)->get();

        $attendanceTrend = $term
            ? Attendance::where('term_id', $term->id)
                ->selectRaw('date, status, count(*) as total')
                ->where('date', '>=', $today->copy()->subDays(13))
                ->groupBy('date', 'status')
                ->orderBy('date')
                ->get()
                ->groupBy('date')
            : collect();

        return view('dashboards.admin', compact('settings', 'session', 'term', 'stats', 'recentStudents', 'recentPayments', 'recentAnnouncements', 'attendanceTrend'));
    }

    protected function teacherDashboard($user)
    {
        $teacher = $user->teacher;
        $settings = SchoolSetting::current();
        $session = $settings->currentAcademicSession;

        $assignments = $teacher
            ? TeacherAssignment::with(['classArm.schoolClass', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->when($session, fn ($q) => $q->where('academic_session_id', $session->id))
                ->get()
            : collect();

        $classArms = $assignments->pluck('classArm')->filter()->unique('id');
        $subjects = $assignments->pluck('subject')->filter()->unique('id');

        $today = Carbon::today();
        $todayAttendance = $teacher
            ? Attendance::whereIn('class_arm_id', $classArms->pluck('id'))->whereDate('date', $today)->count()
            : 0;

        $pendingScoreEntry = $assignments->count();

        $recentAnnouncements = Announcement::published()->forAudience('teachers')->latest('published_at')->take(5)->get();

        return view('dashboards.teacher', compact('teacher', 'assignments', 'classArms', 'subjects', 'todayAttendance', 'pendingScoreEntry', 'recentAnnouncements', 'session'));
    }

    protected function studentDashboard($user)
    {
        $student = $user->student;
        $settings = SchoolSetting::current();
        $term = $settings->currentTerm;

        $attendanceSummary = $student
            ? $student->attendances()->when($term, fn ($q) => $q->where('term_id', $term->id))
                ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')
            : collect();

        $latestResults = $student
            ? $student->results()->where('status', 'published')->when($term, fn ($q) => $q->where('term_id', $term->id))->with('subject')->get()
            : collect();

        $feeStatus = $student
            ? $student->studentFees()->when($term, fn ($q) => $q->where('term_id', $term->id))->get()
            : collect();

        $announcements = Announcement::published()->forAudience('students')->latest('published_at')->take(5)->get();

        return view('dashboards.student', compact('student', 'attendanceSummary', 'latestResults', 'feeStatus', 'announcements', 'term'));
    }

    protected function parentDashboard($user)
    {
        $guardian = $user->guardianProfile;
        $children = $guardian ? $guardian->students()->with('currentClassArm.schoolClass')->get() : collect();

        $selectedChildId = request('child', $children->first()?->id);
        $selectedChild = $children->firstWhere('id', (int) $selectedChildId);

        $settings = SchoolSetting::current();
        $term = $settings->currentTerm;

        $announcements = Announcement::published()->forAudience('parents')->latest('published_at')->take(5)->get();

        return view('dashboards.parent', compact('guardian', 'children', 'selectedChild', 'announcements', 'term'));
    }
}
