<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassArm;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Guardian;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Notifications\AnnouncementPosted;
use App\Notifications\PaymentReceived;
use App\Notifications\ResultPublished;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('administrator');

        return $user;
    }

    protected function makeMinimalResult(): Result
    {
        $session = AcademicSession::factory()->create();
        $term = Term::factory()->create(['academic_session_id' => $session->id]);

        return Result::create([
            'student_id' => Student::factory()->create()->id,
            'subject_id' => Subject::factory()->create()->id,
            'class_arm_id' => ClassArm::factory()->create()->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'status' => 'published',
        ]);
    }

    protected function studentWithGuardian(?ClassArm $classArm = null): array
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('student');
        $student = Student::factory()->create([
            'user_id' => $studentUser->id,
            'current_class_arm_id' => $classArm?->id,
        ]);

        $parentUser = User::factory()->create();
        $parentUser->assignRole('parent');
        $guardian = Guardian::factory()->create(['user_id' => $parentUser->id]);
        $guardian->students()->attach($student->id, ['relationship' => 'Mother', 'is_primary' => true]);

        return compact('studentUser', 'student', 'parentUser', 'guardian');
    }

    public function test_publishing_a_result_notifies_the_student_and_their_guardian(): void
    {
        Notification::fake();

        $session = AcademicSession::factory()->create();
        $term = Term::factory()->create(['academic_session_id' => $session->id]);
        $classArm = ClassArm::factory()->create();
        $subject = Subject::factory()->create();
        ['studentUser' => $studentUser, 'student' => $student, 'parentUser' => $parentUser] = $this->studentWithGuardian($classArm);

        $result = Result::create([
            'student_id' => $student->id, 'subject_id' => $subject->id, 'class_arm_id' => $classArm->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id,
            'assessment_total' => 30, 'examination_score' => 50, 'total_score' => 80,
            'grade' => 'A', 'status' => 'approved',
        ]);

        $this->actingAs($this->admin())->post("/results/{$result->id}/publish")->assertRedirect();

        Notification::assertSentTo($studentUser, ResultPublished::class);
        Notification::assertSentTo($parentUser, ResultPublished::class);
    }

    public function test_recording_a_payment_notifies_the_student_and_their_guardian(): void
    {
        Notification::fake();

        ['studentUser' => $studentUser, 'student' => $student, 'parentUser' => $parentUser] = $this->studentWithGuardian();

        $category = FeeCategory::create(['name' => 'Tuition', 'code' => 'TUITION', 'status' => 'active']);
        $session = AcademicSession::factory()->create();
        $term = Term::factory()->create(['academic_session_id' => $session->id]);
        $structure = FeeStructure::create([
            'fee_category_id' => $category->id, 'academic_session_id' => $session->id,
            'term_id' => $term->id, 'amount' => 50000, 'is_compulsory' => true,
        ]);
        $studentFee = StudentFee::create([
            'student_id' => $student->id, 'fee_structure_id' => $structure->id,
            'academic_session_id' => $session->id, 'term_id' => $term->id,
            'amount_due' => 50000, 'amount_paid' => 0, 'status' => 'unpaid',
        ]);

        $this->actingAs($this->admin())->post('/payments', [
            'student_fee_id' => $studentFee->id,
            'amount' => 20000,
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
        ])->assertRedirect();

        Notification::assertSentTo($studentUser, PaymentReceived::class);
        Notification::assertSentTo($parentUser, PaymentReceived::class);
    }

    public function test_publishing_an_announcement_to_everyone_notifies_all_active_users(): void
    {
        Notification::fake();

        $someUser = User::factory()->create(['is_active' => true]);
        $someUser->assignRole('teacher');
        $inactiveUser = User::factory()->create(['is_active' => false]);
        $inactiveUser->assignRole('teacher');

        $this->actingAs($this->admin())->post('/announcements', [
            'title' => 'School resumes Monday',
            'message' => 'All students should resume by 8am.',
            'target' => 'everyone',
            'status' => 'published',
        ])->assertRedirect();

        Notification::assertSentTo($someUser, AnnouncementPosted::class);
        Notification::assertNotSentTo($inactiveUser, AnnouncementPosted::class);
    }

    public function test_a_class_targeted_announcement_only_notifies_that_classs_students_and_guardians(): void
    {
        Notification::fake();

        $schoolClass = SchoolClass::factory()->create();
        $classArm = ClassArm::factory()->create(['school_class_id' => $schoolClass->id]);
        $otherClassArm = ClassArm::factory()->create();

        ['studentUser' => $inClassStudent, 'parentUser' => $inClassParent] = $this->studentWithGuardian($classArm);
        ['studentUser' => $otherClassStudent] = $this->studentWithGuardian($otherClassArm);

        $this->actingAs($this->admin())->post('/announcements', [
            'title' => 'JSS 1 excursion',
            'message' => 'Permission slips due Friday.',
            'target' => 'class',
            'school_class_id' => $schoolClass->id,
            'status' => 'published',
        ])->assertRedirect();

        Notification::assertSentTo($inClassStudent, AnnouncementPosted::class);
        Notification::assertSentTo($inClassParent, AnnouncementPosted::class);
        Notification::assertNotSentTo($otherClassStudent, AnnouncementPosted::class);
    }

    public function test_a_draft_announcement_does_not_notify_anyone(): void
    {
        Notification::fake();

        $this->actingAs($this->admin())->post('/announcements', [
            'title' => 'Draft notice',
            'message' => 'Not ready yet.',
            'target' => 'everyone',
            'status' => 'draft',
        ])->assertRedirect();

        Notification::assertNothingSent();
    }

    public function test_a_user_can_view_their_notifications_and_mark_one_as_read(): void
    {
        $user = $this->admin();
        $user->notify(new ResultPublished($this->makeMinimalResult()));
        $notification = $user->notifications()->first();

        $this->actingAs($user)->get('/notifications')->assertOk();

        $this->assertNull($notification->fresh()->read_at);
        $this->actingAs($user)->post("/notifications/{$notification->id}/read")->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_a_user_can_mark_all_notifications_as_read(): void
    {
        $user = $this->admin();
        $user->notify(new ResultPublished($this->makeMinimalResult()));
        $user->notify(new ResultPublished($this->makeMinimalResult()));

        $this->assertEquals(2, $user->unreadNotifications()->count());

        $this->actingAs($user)->post('/notifications/read-all')->assertRedirect();

        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_a_user_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = $this->admin();
        $owner->notify(new ResultPublished($this->makeMinimalResult()));
        $notification = $owner->notifications()->first();

        $intruder = $this->admin();

        $this->actingAs($intruder)->post("/notifications/{$notification->id}/read")->assertNotFound();
        $this->assertNull($notification->fresh()->read_at);
    }
}
