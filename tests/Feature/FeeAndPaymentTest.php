<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassArm;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeAndPaymentTest extends TestCase
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

    protected function makeFeeStructure(): FeeStructure
    {
        $category = FeeCategory::create(['name' => 'Tuition', 'code' => 'TUITION', 'status' => 'active']);
        $session = AcademicSession::factory()->create();
        $term = Term::factory()->create(['academic_session_id' => $session->id]);

        return FeeStructure::create([
            'fee_category_id' => $category->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'amount' => 50000,
            'is_compulsory' => true,
        ]);
    }

    public function test_assigning_a_fee_structure_creates_student_fee_records_for_eligible_students(): void
    {
        $structure = $this->makeFeeStructure();
        $classArm = ClassArm::factory()->create();
        $student = Student::factory()->create(['current_class_arm_id' => $classArm->id]);

        $response = $this->actingAs($this->admin())->post("/fee-structures/{$structure->id}/assign");

        $response->assertRedirect();
        $this->assertDatabaseHas('student_fees', [
            'student_id' => $student->id,
            'fee_structure_id' => $structure->id,
            'amount_due' => 50000,
            'status' => 'unpaid',
        ]);
    }

    public function test_a_partial_payment_updates_balance_and_marks_the_fee_as_partial(): void
    {
        $structure = $this->makeFeeStructure();
        $student = Student::factory()->create();

        $studentFee = StudentFee::create([
            'student_id' => $student->id,
            'fee_structure_id' => $structure->id,
            'academic_session_id' => $structure->academic_session_id,
            'term_id' => $structure->term_id,
            'amount_due' => 50000,
            'amount_paid' => 0,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->admin())->post('/payments', [
            'student_fee_id' => $studentFee->id,
            'amount' => 20000,
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();

        $studentFee->refresh();
        $this->assertEquals(20000, (float) $studentFee->amount_paid);
        $this->assertEquals(30000, $studentFee->balance);
        $this->assertEquals('partial', $studentFee->status);

        $payment = Payment::first();
        $this->assertNotEmpty($payment->receipt_number);
    }

    public function test_a_full_payment_marks_the_fee_as_paid(): void
    {
        $structure = $this->makeFeeStructure();
        $student = Student::factory()->create();

        $studentFee = StudentFee::create([
            'student_id' => $student->id,
            'fee_structure_id' => $structure->id,
            'academic_session_id' => $structure->academic_session_id,
            'term_id' => $structure->term_id,
            'amount_due' => 50000,
            'amount_paid' => 0,
            'status' => 'unpaid',
        ]);

        $this->actingAs($this->admin())->post('/payments', [
            'student_fee_id' => $studentFee->id,
            'amount' => 50000,
            'payment_method' => 'bank_transfer',
            'payment_date' => now()->format('Y-m-d'),
        ]);

        $studentFee->refresh();
        $this->assertEquals('paid', $studentFee->status);
        $this->assertEquals(0, $studentFee->balance);
    }

    public function test_payment_cannot_exceed_the_outstanding_balance(): void
    {
        $structure = $this->makeFeeStructure();
        $student = Student::factory()->create();

        $studentFee = StudentFee::create([
            'student_id' => $student->id,
            'fee_structure_id' => $structure->id,
            'academic_session_id' => $structure->academic_session_id,
            'term_id' => $structure->term_id,
            'amount_due' => 50000,
            'amount_paid' => 0,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->admin())->post('/payments', [
            'student_fee_id' => $studentFee->id,
            'amount' => 60000,
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHas('error');
        $studentFee->refresh();
        $this->assertEquals(0, (float) $studentFee->amount_paid);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_receipt_numbers_are_unique(): void
    {
        $structure = $this->makeFeeStructure();

        $fee1 = StudentFee::create([
            'student_id' => Student::factory()->create()->id, 'fee_structure_id' => $structure->id,
            'academic_session_id' => $structure->academic_session_id, 'term_id' => $structure->term_id,
            'amount_due' => 10000, 'amount_paid' => 0, 'status' => 'unpaid',
        ]);
        $fee2 = StudentFee::create([
            'student_id' => Student::factory()->create()->id, 'fee_structure_id' => $structure->id,
            'academic_session_id' => $structure->academic_session_id, 'term_id' => $structure->term_id,
            'amount_due' => 10000, 'amount_paid' => 0, 'status' => 'unpaid',
        ]);

        $p1 = Payment::create(['student_fee_id' => $fee1->id, 'amount' => 1000, 'payment_method' => 'cash', 'payment_date' => now()]);
        $p2 = Payment::create(['student_fee_id' => $fee2->id, 'amount' => 1000, 'payment_method' => 'cash', 'payment_date' => now()]);

        $this->assertNotEquals($p1->receipt_number, $p2->receipt_number);
    }
}
