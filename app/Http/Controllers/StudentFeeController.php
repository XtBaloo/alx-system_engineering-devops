<?php

namespace App\Http\Controllers;

use App\Models\ClassArm;
use App\Models\Student;
use App\Models\StudentFee;
use Illuminate\Http\Request;

class StudentFeeController extends Controller
{
    public function outstanding(Request $request)
    {
        abort_unless($request->user()->can('manage-fees'), 403);

        $fees = StudentFee::with(['student.currentClassArm.schoolClass', 'feeStructure.feeCategory', 'term'])
            ->outstanding()
            ->when($request->class_arm_id, fn ($q, $id) => $q->whereHas('student', fn ($q2) => $q2->where('current_class_arm_id', $id)))
            ->latest('id')
            ->paginate(30)->withQueryString();

        $classArms = ClassArm::with('schoolClass')->get();
        $totalOutstanding = (clone $fees->getCollection())->sum(fn ($f) => $f->balance);

        return view('student-fees.outstanding', compact('fees', 'classArms', 'totalOutstanding'));
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student->studentFees()->first() ?? new \App\Models\StudentFee(['student_id' => $student->id]));

        $fees = $student->studentFees()->with(['feeStructure.feeCategory', 'term', 'payments'])->latest('id')->get();

        return view('student-fees.show', compact('student', 'fees'));
    }
}
