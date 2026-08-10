<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Term;
use Illuminate\Database\Seeder;

class FeeSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Tuition', 'code' => 'TUITION'],
            ['name' => 'Registration', 'code' => 'REGISTRATION'],
            ['name' => 'Examination', 'code' => 'EXAM'],
            ['name' => 'Development Levy', 'code' => 'DEV_LEVY'],
            ['name' => 'ICT', 'code' => 'ICT'],
            ['name' => 'Sports', 'code' => 'SPORTS'],
            ['name' => 'Uniform', 'code' => 'UNIFORM'],
            ['name' => 'Textbooks', 'code' => 'TEXTBOOKS'],
            ['name' => 'Transportation', 'code' => 'TRANSPORT'],
            ['name' => 'Other', 'code' => 'OTHER'],
        ];

        foreach ($categories as $c) {
            FeeCategory::updateOrCreate(['code' => $c['code']], $c + ['status' => 'active']);
        }

        $session = AcademicSession::where('is_active', true)->first();
        $term = Term::where('is_active', true)->first();

        $tuition = FeeCategory::where('code', 'TUITION')->first();
        $devLevy = FeeCategory::where('code', 'DEV_LEVY')->first();

        $tuitionStructure = FeeStructure::updateOrCreate([
            'fee_category_id' => $tuition->id, 'school_class_id' => null,
            'academic_session_id' => $session->id, 'term_id' => $term->id,
        ], ['amount' => 75000, 'is_compulsory' => true]);

        $levyStructure = FeeStructure::updateOrCreate([
            'fee_category_id' => $devLevy->id, 'school_class_id' => null,
            'academic_session_id' => $session->id, 'term_id' => $term->id,
        ], ['amount' => 10000, 'is_compulsory' => true]);

        foreach (Student::active()->get() as $student) {
            foreach ([$tuitionStructure, $levyStructure] as $structure) {
                StudentFee::firstOrCreate([
                    'student_id' => $student->id,
                    'fee_structure_id' => $structure->id,
                ], [
                    'academic_session_id' => $session->id,
                    'term_id' => $term->id,
                    'amount_due' => $structure->amount,
                    'amount_paid' => 0,
                    'status' => 'unpaid',
                ]);
            }
        }

        // Record a partial payment for the demo student to showcase the workflow
        $demoStudent = Student::where('admission_number', 'PFA/26/0001')->first();
        $demoFee = StudentFee::where('student_id', $demoStudent->id)->where('fee_structure_id', $tuitionStructure->id)->first();

        if ($demoFee && $demoFee->amount_paid == 0) {
            $payment = Payment::create([
                'student_fee_id' => $demoFee->id,
                'receipt_number' => Payment::generateReceiptNumber(),
                'amount' => 40000,
                'payment_method' => 'bank_transfer',
                'payment_date' => now()->subDays(5)->format('Y-m-d'),
                'notes' => 'Part payment for first term tuition.',
            ]);

            $demoFee->update(['amount_paid' => 40000, 'status' => 'partial']);
        }
    }
}
