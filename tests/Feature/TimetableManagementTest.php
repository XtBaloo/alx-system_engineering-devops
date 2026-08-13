<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassArm;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimetableManagementTest extends TestCase
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

    protected function makeTeacherUser(): Teacher
    {
        $user = User::factory()->create();
        $user->assignRole('teacher');

        return Teacher::factory()->create(['user_id' => $user->id]);
    }

    protected function baseEntry(array $overrides = []): array
    {
        AcademicSession::factory()->active()->create();

        return array_merge([
            'class_arm_id' => ClassArm::factory()->create()->id,
            'subject_id' => Subject::factory()->create()->id,
            'teacher_id' => Teacher::factory()->create()->id,
            'day_of_week' => 'monday',
            'start_time' => '08:00',
            'end_time' => '08:45',
            'room' => 'Room 1',
        ], $overrides);
    }

    public function test_administrator_can_create_a_timetable_entry(): void
    {
        $data = $this->baseEntry();

        $response = $this->actingAs($this->admin())->post('/timetable', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('timetable_entries', [
            'class_arm_id' => $data['class_arm_id'],
            'subject_id' => $data['subject_id'],
            'teacher_id' => $data['teacher_id'],
            'day_of_week' => 'monday',
            'room' => 'Room 1',
        ]);
    }

    public function test_administrator_can_update_a_timetable_entry(): void
    {
        $data = $this->baseEntry();
        $this->actingAs($admin = $this->admin())->post('/timetable', $data);
        $entry = TimetableEntry::first();

        $response = $this->actingAs($admin)->put("/timetable/{$entry->id}", array_merge($data, [
            'room' => 'Room 9',
            'start_time' => '09:00',
            'end_time' => '09:45',
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('timetable_entries', [
            'id' => $entry->id,
            'room' => 'Room 9',
        ]);
    }

    public function test_administrator_can_delete_a_timetable_entry(): void
    {
        $data = $this->baseEntry();
        $this->actingAs($admin = $this->admin())->post('/timetable', $data);
        $entry = TimetableEntry::first();

        $response = $this->actingAs($admin)->delete("/timetable/{$entry->id}");

        $response->assertRedirect();
        $this->assertDatabaseCount('timetable_entries', 0);
    }

    public function test_timetable_entry_creation_requires_mandatory_fields(): void
    {
        $response = $this->actingAs($this->admin())->post('/timetable', []);

        $response->assertSessionHasErrors([
            'class_arm_id', 'subject_id', 'teacher_id', 'day_of_week', 'start_time', 'end_time', 'room',
        ]);
        $this->assertDatabaseCount('timetable_entries', 0);
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $data = $this->baseEntry(['start_time' => '10:00', 'end_time' => '09:00']);

        $response = $this->actingAs($this->admin())->post('/timetable', $data);

        $response->assertSessionHasErrors('end_time');
        $this->assertDatabaseCount('timetable_entries', 0);
    }

    public function test_a_teacher_cannot_be_double_booked_at_an_overlapping_time(): void
    {
        $teacher = Teacher::factory()->create();
        $data = $this->baseEntry(['teacher_id' => $teacher->id]);
        $this->actingAs($admin = $this->admin())->post('/timetable', $data);

        $conflicting = $this->baseEntry([
            'teacher_id' => $teacher->id,
            'class_arm_id' => ClassArm::factory()->create()->id,
            'day_of_week' => 'monday',
            'start_time' => '08:30',
            'end_time' => '09:15',
            'room' => 'Room 2',
        ]);

        $response = $this->actingAs($admin)->post('/timetable', $conflicting);

        $response->assertSessionHasErrors('teacher_id');
        $this->assertDatabaseCount('timetable_entries', 1);
    }

    public function test_a_class_cannot_have_two_subjects_at_an_overlapping_time(): void
    {
        $classArm = ClassArm::factory()->create();
        $data = $this->baseEntry(['class_arm_id' => $classArm->id]);
        $this->actingAs($admin = $this->admin())->post('/timetable', $data);

        $conflicting = $this->baseEntry([
            'class_arm_id' => $classArm->id,
            'teacher_id' => Teacher::factory()->create()->id,
            'day_of_week' => 'monday',
            'start_time' => '08:15',
            'end_time' => '09:00',
            'room' => 'Room 2',
        ]);

        $response = $this->actingAs($admin)->post('/timetable', $conflicting);

        $response->assertSessionHasErrors('class_arm_id');
        $this->assertDatabaseCount('timetable_entries', 1);
    }

    public function test_a_room_cannot_be_double_booked_at_an_overlapping_time(): void
    {
        $data = $this->baseEntry(['room' => 'Hall A']);
        $this->actingAs($admin = $this->admin())->post('/timetable', $data);

        $conflicting = $this->baseEntry([
            'class_arm_id' => ClassArm::factory()->create()->id,
            'teacher_id' => Teacher::factory()->create()->id,
            'day_of_week' => 'monday',
            'start_time' => '08:20',
            'end_time' => '09:00',
            'room' => 'Hall A',
        ]);

        $response = $this->actingAs($admin)->post('/timetable', $conflicting);

        $response->assertSessionHasErrors('room');
        $this->assertDatabaseCount('timetable_entries', 1);
    }

    public function test_non_overlapping_entries_for_the_same_teacher_are_allowed(): void
    {
        $teacher = Teacher::factory()->create();
        $data = $this->baseEntry(['teacher_id' => $teacher->id, 'start_time' => '08:00', 'end_time' => '08:45']);
        $this->actingAs($admin = $this->admin())->post('/timetable', $data);

        $backToBack = $this->baseEntry([
            'teacher_id' => $teacher->id,
            'class_arm_id' => ClassArm::factory()->create()->id,
            'day_of_week' => 'monday',
            'start_time' => '08:45',
            'end_time' => '09:30',
            'room' => 'Room 2',
        ]);

        $response = $this->actingAs($admin)->post('/timetable', $backToBack);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseCount('timetable_entries', 2);
    }

    public function test_updating_an_entry_does_not_conflict_with_itself(): void
    {
        $data = $this->baseEntry();
        $this->actingAs($admin = $this->admin())->post('/timetable', $data);
        $entry = TimetableEntry::first();

        $response = $this->actingAs($admin)->put("/timetable/{$entry->id}", array_merge($data, [
            'room' => 'Room 1',
        ]));

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect();
    }

    public function test_administrator_can_filter_the_timetable_by_class(): void
    {
        $session = AcademicSession::factory()->active()->create();
        $classA = ClassArm::factory()->create();
        $classB = ClassArm::factory()->create();
        $subjectA = Subject::factory()->create(['name' => 'Mathematics']);
        $subjectB = Subject::factory()->create(['name' => 'English Language']);
        $teacher = Teacher::factory()->create();

        TimetableEntry::factory()->create([
            'academic_session_id' => $session->id, 'class_arm_id' => $classA->id, 'subject_id' => $subjectA->id, 'teacher_id' => $teacher->id,
        ]);
        TimetableEntry::factory()->create([
            'academic_session_id' => $session->id, 'class_arm_id' => $classB->id, 'subject_id' => $subjectB->id, 'teacher_id' => $teacher->id,
        ]);

        $response = $this->actingAs($this->admin())->get('/timetable?class_arm_id='.$classA->id);

        $response->assertOk()->assertSee('Mathematics')->assertDontSee('English Language');
    }

    public function test_a_teacher_can_view_their_own_timetable(): void
    {
        $session = AcademicSession::factory()->active()->create();
        $teacher = $this->makeTeacherUser();
        $subject = Subject::factory()->create(['name' => 'Physics']);

        TimetableEntry::factory()->create([
            'academic_session_id' => $session->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
        ]);

        $response = $this->actingAs($teacher->user)->get('/my/timetable');

        $response->assertOk()->assertSee('Physics');
    }

    public function test_a_teacher_only_sees_their_own_entries_on_my_timetable(): void
    {
        $session = AcademicSession::factory()->active()->create();
        $teacher = $this->makeTeacherUser();
        $otherTeacher = Teacher::factory()->create();
        $mySubject = Subject::factory()->create(['name' => 'Chemistry']);
        $otherSubject = Subject::factory()->create(['name' => 'History']);

        TimetableEntry::factory()->create(['academic_session_id' => $session->id, 'teacher_id' => $teacher->id, 'subject_id' => $mySubject->id]);
        TimetableEntry::factory()->create(['academic_session_id' => $session->id, 'teacher_id' => $otherTeacher->id, 'subject_id' => $otherSubject->id]);

        $response = $this->actingAs($teacher->user)->get('/my/timetable');

        $response->assertOk()->assertSee('Chemistry')->assertDontSee('History');
    }

    public function test_a_teacher_cannot_access_timetable_management(): void
    {
        $teacherUser = User::factory()->create();
        $teacherUser->assignRole('teacher');

        $this->actingAs($teacherUser)->get('/timetable')->assertForbidden();
        $this->actingAs($teacherUser)->get('/timetable/create')->assertForbidden();
        $this->actingAs($teacherUser)->post('/timetable', $this->baseEntry())->assertForbidden();
    }

    public function test_a_student_cannot_access_any_timetable_route(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('student');

        $this->actingAs($studentUser)->get('/timetable')->assertForbidden();
        $this->actingAs($studentUser)->get('/my/timetable')->assertForbidden();
    }
}
