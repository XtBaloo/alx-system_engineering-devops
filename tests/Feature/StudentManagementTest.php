<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassArm;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementTest extends TestCase
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

    public function test_administrator_can_register_a_new_student_with_a_generated_admission_number(): void
    {
        $classArm = ClassArm::factory()->create();
        AcademicSession::factory()->active()->create();

        $response = $this->actingAs($this->admin())->post('/students', [
            'first_name' => 'David',
            'last_name' => 'Nwosu',
            'gender' => 'male',
            'admission_date' => now()->format('Y-m-d'),
            'current_class_arm_id' => $classArm->id,
            'status' => 'active',
        ]);

        $student = Student::first();

        $response->assertRedirect(route('students.show', $student));
        $this->assertNotNull($student);
        $this->assertNotEmpty($student->admission_number);
        $this->assertEquals($classArm->id, $student->current_class_arm_id);
    }

    public function test_student_creation_requires_mandatory_fields(): void
    {
        $response = $this->actingAs($this->admin())->post('/students', []);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'gender', 'admission_date', 'current_class_arm_id']);
        $this->assertDatabaseCount('students', 0);
    }

    public function test_a_new_guardian_can_be_created_and_linked_during_student_registration(): void
    {
        $classArm = ClassArm::factory()->create();
        AcademicSession::factory()->active()->create();

        $this->actingAs($this->admin())->post('/students', [
            'first_name' => 'Amaka',
            'last_name' => 'Obi',
            'gender' => 'female',
            'admission_date' => now()->format('Y-m-d'),
            'current_class_arm_id' => $classArm->id,
            'status' => 'active',
            'guardian_relationship' => 'Mother',
            'new_guardian_first_name' => 'Blessing',
            'new_guardian_last_name' => 'Obi',
        ]);

        $student = Student::first();

        $this->assertCount(1, $student->guardians);
        $this->assertEquals('Blessing', $student->guardians->first()->first_name);
        $this->assertTrue((bool) $student->guardians->first()->pivot->is_primary);
    }

    public function test_teacher_cannot_create_a_student(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        $classArm = ClassArm::factory()->create();

        $response = $this->actingAs($teacher)->post('/students', [
            'first_name' => 'Test',
            'last_name' => 'Student',
            'gender' => 'male',
            'admission_date' => now()->format('Y-m-d'),
            'current_class_arm_id' => $classArm->id,
            'status' => 'active',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('students', 0);
    }
}
