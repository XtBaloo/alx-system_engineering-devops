<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\ClassArm;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function makeTeacherUser(ClassArm $classArm): User
    {
        $user = User::factory()->create();
        $user->assignRole('teacher');

        $teacher = Teacher::factory()->create(['user_id' => $user->id]);
        $classArm->update(['class_teacher_id' => $teacher->id]);

        return $user;
    }

    public function test_class_teacher_can_record_attendance_for_their_class(): void
    {
        $session = AcademicSession::factory()->active()->create();
        $term = Term::factory()->active()->create(['academic_session_id' => $session->id]);
        $classArm = ClassArm::factory()->create();
        $teacherUser = $this->makeTeacherUser($classArm);
        $student = Student::factory()->create(['current_class_arm_id' => $classArm->id]);

        $response = $this->actingAs($teacherUser)->post('/attendance/take', [
            'class_arm_id' => $classArm->id,
            'date' => now()->format('Y-m-d'),
            'statuses' => [$student->id => 'present'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance', [
            'student_id' => $student->id,
            'class_arm_id' => $classArm->id,
            'status' => 'present',
        ]);
    }

    public function test_duplicate_attendance_for_the_same_student_and_date_updates_instead_of_duplicating(): void
    {
        $session = AcademicSession::factory()->active()->create();
        $term = Term::factory()->active()->create(['academic_session_id' => $session->id]);
        $classArm = ClassArm::factory()->create();
        $teacherUser = $this->makeTeacherUser($classArm);
        $student = Student::factory()->create(['current_class_arm_id' => $classArm->id]);
        $date = now()->format('Y-m-d');

        $this->actingAs($teacherUser)->post('/attendance/take', [
            'class_arm_id' => $classArm->id,
            'date' => $date,
            'statuses' => [$student->id => 'present'],
        ]);

        $this->actingAs($teacherUser)->post('/attendance/take', [
            'class_arm_id' => $classArm->id,
            'date' => $date,
            'statuses' => [$student->id => 'absent'],
        ]);

        $this->assertEquals(1, Attendance::where('student_id', $student->id)->whereDate('date', $date)->count());
        $this->assertEquals('absent', Attendance::where('student_id', $student->id)->whereDate('date', $date)->first()->status);
    }

    public function test_a_teacher_not_assigned_to_the_class_cannot_take_its_attendance(): void
    {
        $session = AcademicSession::factory()->active()->create();
        $term = Term::factory()->active()->create(['academic_session_id' => $session->id]);
        $classArm = ClassArm::factory()->create();
        $student = Student::factory()->create(['current_class_arm_id' => $classArm->id]);

        $unrelatedTeacherUser = User::factory()->create();
        $unrelatedTeacherUser->assignRole('teacher');
        Teacher::factory()->create(['user_id' => $unrelatedTeacherUser->id]);

        $response = $this->actingAs($unrelatedTeacherUser)->post('/attendance/take', [
            'class_arm_id' => $classArm->id,
            'date' => now()->format('Y-m-d'),
            'statuses' => [$student->id => 'present'],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('attendance', 0);
    }
}
