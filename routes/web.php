<?php

use App\Http\Controllers\AcademicSessionController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssessmentTypeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ClassArmController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FeeCategoryController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\GradingScaleController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\MyPortalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\ScoreEntryController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentFeeController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherAssignmentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TermController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    // School Settings
    Route::middleware('permission:manage-settings')->group(function () {
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::resource('grading-scales', GradingScaleController::class)->except(['show']);
    });

    // Academic structure
    Route::middleware('permission:manage-academic-structure')->group(function () {
        Route::resource('academic-sessions', AcademicSessionController::class)->except(['show']);
        Route::post('academic-sessions/{academic_session}/activate', [AcademicSessionController::class, 'activate'])->name('academic-sessions.activate');

        Route::resource('terms', TermController::class)->except(['show']);
        Route::post('terms/{term}/activate', [TermController::class, 'activate'])->name('terms.activate');
        Route::post('terms/{term}/close', [TermController::class, 'close'])->name('terms.close');
        Route::post('terms/{term}/reopen', [TermController::class, 'reopen'])->name('terms.reopen');

        Route::resource('classes', SchoolClassController::class)->except(['show']);
        Route::resource('class-arms', ClassArmController::class)->except(['show']);
        Route::resource('subjects', SubjectController::class)->except(['show']);
        Route::resource('teacher-assignments', TeacherAssignmentController::class)->only(['index', 'create', 'store', 'destroy']);
        Route::resource('timetable', TimetableController::class)->except(['show']);
    });

    Route::middleware('permission:view-timetable')->group(function () {
        Route::get('my/timetable', [TimetableController::class, 'mine'])->name('timetable.mine');
    });

    // Teachers
    Route::resource('teachers', TeacherController::class);

    // Students
    Route::get('students/promotions', [StudentController::class, 'promotions'])->name('students.promotions');
    Route::post('students/promotions', [StudentController::class, 'promote'])->name('students.promote');
    Route::post('students/{student}/reverse-promotion/{enrollment}', [StudentController::class, 'reversePromotion'])->name('students.reverse-promotion');
    Route::resource('students', StudentController::class);
    Route::middleware('permission:manage-students')->group(function () {
        Route::resource('guardians', GuardianController::class);
    });
    Route::post('students/{student}/guardians', [StudentController::class, 'attachGuardian'])->name('students.guardians.attach');
    Route::delete('students/{student}/guardians/{guardian}', [StudentController::class, 'detachGuardian'])->name('students.guardians.detach');

    // Documents
    Route::post('students/{student}/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Attendance
    Route::middleware('permission:take-attendance')->group(function () {
        Route::get('attendance/take', [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('attendance/take', [AttendanceController::class, 'store'])->name('attendance.store');
    });
    Route::middleware('permission:view-attendance')->group(function () {
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    });

    // Assessment / Results
    Route::middleware('permission:manage-assessment-config')->group(function () {
        Route::resource('assessment-types', AssessmentTypeController::class)->except(['show']);
    });
    Route::middleware('permission:enter-scores')->group(function () {
        Route::get('scores', [ScoreEntryController::class, 'create'])->name('scores.create');
        Route::post('scores', [ScoreEntryController::class, 'store'])->name('scores.store');
    });

    Route::middleware('permission:view-results')->group(function () {
        Route::get('results', [ResultController::class, 'index'])->name('results.index');
        Route::get('results/published', [ResultController::class, 'published'])->name('results.published');
    });
    Route::post('results/{result}/submit', [ResultController::class, 'submit'])->name('results.submit');
    Route::post('results/submit-batch', [ResultController::class, 'submitBatch'])->name('results.submit-batch');
    Route::post('results/{result}/review', [ResultController::class, 'review'])->name('results.review');
    Route::post('results/{result}/approve', [ResultController::class, 'approve'])->name('results.approve');
    Route::post('results/{result}/publish', [ResultController::class, 'publish'])->name('results.publish');
    Route::post('results/publish-batch', [ResultController::class, 'publishBatch'])->name('results.publish-batch');

    // Report cards
    Route::get('report-cards', [ReportCardController::class, 'select'])->name('report-cards.select');
    Route::get('report-cards/{student}', [ReportCardController::class, 'show'])->name('report-cards.show');
    Route::get('report-cards/{student}/pdf', [ReportCardController::class, 'pdf'])->name('report-cards.pdf');

    // Finance
    Route::middleware('permission:manage-fees')->group(function () {
        Route::resource('fee-categories', FeeCategoryController::class)->except(['show']);
        Route::resource('fee-structures', FeeStructureController::class)->except(['show']);
        Route::post('fee-structures/{fee_structure}/assign', [FeeStructureController::class, 'assign'])->name('fee-structures.assign');
    });
    Route::get('student-fees/outstanding', [StudentFeeController::class, 'outstanding'])->name('student-fees.outstanding');
    Route::get('students/{student}/fees', [StudentFeeController::class, 'show'])->name('student-fees.show');

    Route::middleware('permission:manage-payments')->group(function () {
        Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store']);
    });
    Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('payments/{payment}/receipt/pdf', [PaymentController::class, 'receiptPdf'])->name('payments.receipt.pdf');

    // Announcements
    Route::resource('announcements', AnnouncementController::class)->except(['show']);

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/students', [ReportController::class, 'students'])->name('reports.students');
    Route::get('reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('reports/academic', [ReportController::class, 'academic'])->name('reports.academic');
    Route::get('reports/finance', [ReportController::class, 'finance'])->name('reports.finance');
    Route::get('reports/teachers', [ReportController::class, 'teachers'])->name('reports.teachers');

    // My portal (student / parent)
    Route::get('my/results', [MyPortalController::class, 'results'])->name('my.results');
    Route::get('my/attendance', [MyPortalController::class, 'attendance'])->name('my.attendance');
    Route::get('my/fees', [MyPortalController::class, 'fees'])->name('my.fees');
    Route::get('my/children/{student}/switch', [MyPortalController::class, 'switchChild'])->name('my.switch-child');

    // Admin: users
    Route::middleware('permission:manage-users')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });

    // Audit logs
    Route::middleware('permission:view-audit-logs')->group(function () {
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});

require __DIR__.'/auth.php';
