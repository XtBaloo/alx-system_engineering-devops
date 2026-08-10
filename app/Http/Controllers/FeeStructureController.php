<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeStructureController extends Controller
{
    public function index()
    {
        $structures = FeeStructure::with(['feeCategory', 'schoolClass', 'academicSession', 'term'])
            ->latest('id')->paginate(20);

        return view('fee-structures.index', compact('structures'));
    }

    public function create()
    {
        $categories = FeeCategory::active()->orderBy('name')->get();
        $classes = SchoolClass::ordered()->get();
        $sessions = AcademicSession::orderByDesc('start_date')->get();
        $terms = Term::orderByDesc('id')->get();
        $currentSessionId = SchoolSetting::current()->current_academic_session_id;
        $currentTermId = SchoolSetting::current()->current_term_id;

        return view('fee-structures.create', compact('categories', 'classes', 'sessions', 'terms', 'currentSessionId', 'currentTermId'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        FeeStructure::create($data);

        return redirect()->route('fee-structures.index')->with('success', 'Fee structure created.');
    }

    public function edit(FeeStructure $feeStructure)
    {
        $categories = FeeCategory::active()->orderBy('name')->get();
        $classes = SchoolClass::ordered()->get();
        $sessions = AcademicSession::orderByDesc('start_date')->get();
        $terms = Term::orderByDesc('id')->get();

        return view('fee-structures.edit', compact('feeStructure', 'categories', 'classes', 'sessions', 'terms'));
    }

    public function update(Request $request, FeeStructure $feeStructure)
    {
        $data = $this->validated($request);
        $feeStructure->update($data);

        return redirect()->route('fee-structures.index')->with('success', 'Fee structure updated.');
    }

    public function destroy(FeeStructure $feeStructure)
    {
        if ($feeStructure->studentFees()->exists()) {
            return back()->with('error', 'Cannot delete a fee structure already assigned to students.');
        }

        $feeStructure->delete();

        return back()->with('success', 'Fee structure removed.');
    }

    public function assign(FeeStructure $feeStructure)
    {
        $students = $feeStructure->school_class_id
            ? Student::active()->whereHas('currentClassArm', fn ($q) => $q->where('school_class_id', $feeStructure->school_class_id))->get()
            : Student::active()->get();

        $created = 0;

        DB::transaction(function () use ($students, $feeStructure, &$created) {
            foreach ($students as $student) {
                $exists = StudentFee::where('student_id', $student->id)->where('fee_structure_id', $feeStructure->id)->exists();

                if (! $exists) {
                    StudentFee::create([
                        'student_id' => $student->id,
                        'fee_structure_id' => $feeStructure->id,
                        'academic_session_id' => $feeStructure->academic_session_id,
                        'term_id' => $feeStructure->term_id,
                        'amount_due' => $feeStructure->amount,
                        'amount_paid' => 0,
                        'status' => 'unpaid',
                    ]);
                    $created++;
                }
            }
        });

        return back()->with('success', "Fee assigned to {$created} student(s).");
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'fee_category_id' => ['required', 'exists:fee_categories,id'],
            'school_class_id' => ['nullable', 'exists:classes,id'],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $data['is_compulsory'] = $request->boolean('is_compulsory');

        return $data;
    }
}
