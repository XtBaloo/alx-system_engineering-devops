<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\ClassArm;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        $students = Student::with('currentClassArm.schoolClass')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->class_arm_id, fn ($q, $armId) => $q->where('current_class_arm_id', $armId))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('first_name')
            ->paginate(20)->withQueryString();

        $classArms = ClassArm::with('schoolClass')->get();

        return view('students.index', compact('students', 'classArms'));
    }

    public function create()
    {
        $this->authorize('create', Student::class);

        $classArms = ClassArm::with('schoolClass')->get();
        $guardians = Guardian::orderBy('first_name')->get();

        return view('students.create', compact('classArms', 'guardians'));
    }

    public function store(StoreStudentRequest $request)
    {
        $data = $request->validated();
        $createLogin = $request->boolean('create_login');

        $student = DB::transaction(function () use ($data, $request, $createLogin) {
            $studentData = collect($data)->only([
                'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth',
                'nationality', 'state_of_origin', 'lga', 'address', 'phone', 'email',
                'admission_date', 'previous_school', 'current_class_arm_id', 'status',
            ])->toArray();

            $studentData['admission_number'] = $this->generateAdmissionNumber();

            if ($request->hasFile('photo')) {
                $studentData['photo_path'] = $request->file('photo')->store('students', 'public');
            }

            if ($createLogin && ! empty($studentData['email'])) {
                $user = User::create([
                    'name' => trim($studentData['first_name'].' '.$studentData['last_name']),
                    'email' => $studentData['email'],
                    'password' => Hash::make(Str::random(12)),
                ]);
                $user->assignRole('student');
                $studentData['user_id'] = $user->id;
            }

            $student = Student::create($studentData);

            // Link guardian (existing or new)
            if (! empty($data['guardian_id'])) {
                $student->guardians()->attach($data['guardian_id'], [
                    'relationship' => $data['guardian_relationship'] ?? 'guardian',
                    'is_primary' => true,
                ]);
            } elseif (! empty($data['new_guardian_first_name'])) {
                $guardian = Guardian::create([
                    'first_name' => $data['new_guardian_first_name'],
                    'last_name' => $data['new_guardian_last_name'],
                    'phone' => $data['new_guardian_phone'] ?? null,
                    'email' => $data['new_guardian_email'] ?? null,
                    'occupation' => $data['new_guardian_occupation'] ?? null,
                ]);
                $student->guardians()->attach($guardian->id, [
                    'relationship' => $data['guardian_relationship'] ?? 'guardian',
                    'is_primary' => true,
                ]);
            }

            // Create initial enrollment for the current academic session
            $classArm = ClassArm::find($studentData['current_class_arm_id']);
            $session = SchoolSetting::current()->currentAcademicSession;

            if ($session && $classArm) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'academic_session_id' => $session->id,
                    'school_class_id' => $classArm->school_class_id,
                    'class_arm_id' => $classArm->id,
                    'status' => 'active',
                ]);
            }

            return $student;
        });

        return redirect()->route('students.show', $student)->with('success', 'Student admitted successfully.');
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student);

        $student->load([
            'currentClassArm.schoolClass', 'guardians', 'documents',
            'enrollments' => fn ($q) => $q->latest('id'),
            'enrollments.schoolClass', 'enrollments.classArm', 'enrollments.academicSession', 'enrollments.promotedFrom',
        ]);

        $attendanceSummary = $student->attendances()
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $results = $student->results()->with(['subject', 'term'])->where('status', 'published')->latest('id')->get();

        $studentFees = $student->studentFees()->with(['feeStructure.feeCategory', 'payments'])->latest('id')->get();

        $allGuardians = Guardian::orderBy('first_name')->get();

        return view('students.show', compact('student', 'attendanceSummary', 'results', 'studentFees', 'allGuardians'));
    }

    public function edit(Student $student)
    {
        $this->authorize('update', $student);

        $classArms = ClassArm::with('schoolClass')->get();

        return view('students.edit', compact('student', 'classArms'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('students', 'public');
        }

        $student->update($data);

        return redirect()->route('students.show', $student)->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);

        $student->update(['status' => 'archived']);
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student archived.');
    }

    public function attachGuardian(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $data = $request->validate([
            'guardian_id' => ['nullable', 'exists:guardians,id'],
            'relationship' => ['required', 'string', 'max:100'],
            'is_primary' => ['boolean'],
            'new_guardian_first_name' => ['nullable', 'string', 'max:255'],
            'new_guardian_last_name' => ['nullable', 'string', 'max:255'],
            'new_guardian_phone' => ['nullable', 'string', 'max:50'],
            'new_guardian_email' => ['nullable', 'email', 'max:255'],
            'new_guardian_occupation' => ['nullable', 'string', 'max:255'],
        ]);

        $guardianId = $data['guardian_id'] ?? null;

        if (! $guardianId && ! empty($data['new_guardian_first_name'])) {
            $guardian = Guardian::create([
                'first_name' => $data['new_guardian_first_name'],
                'last_name' => $data['new_guardian_last_name'] ?? '',
                'phone' => $data['new_guardian_phone'] ?? null,
                'email' => $data['new_guardian_email'] ?? null,
                'occupation' => $data['new_guardian_occupation'] ?? null,
            ]);
            $guardianId = $guardian->id;
        }

        if ($guardianId) {
            $student->guardians()->syncWithoutDetaching([
                $guardianId => ['relationship' => $data['relationship'], 'is_primary' => $request->boolean('is_primary')],
            ]);
        }

        return back()->with('success', 'Guardian linked to student.');
    }

    public function detachGuardian(Student $student, Guardian $guardian)
    {
        $this->authorize('update', $student);

        $student->guardians()->detach($guardian->id);

        return back()->with('success', 'Guardian unlinked from student.');
    }

    public function promotions()
    {
        $this->authorize('viewAny', Student::class);

        $classArms = ClassArm::with('schoolClass')->get();
        $sourceArmId = request('source_class_arm_id');
        $students = $sourceArmId
            ? Student::active()->where('current_class_arm_id', $sourceArmId)->orderBy('first_name')->get()
            : collect();

        return view('students.promotions', compact('classArms', 'students', 'sourceArmId'));
    }

    public function promote(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        $data = $request->validate([
            'target_class_arm_id' => ['required', 'exists:class_arms,id'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['exists:students,id'],
        ]);

        $targetArm = ClassArm::findOrFail($data['target_class_arm_id']);
        $session = SchoolSetting::current()->currentAcademicSession;

        if (! $session) {
            return back()->with('error', 'Please set an active academic session before promoting students.');
        }

        DB::transaction(function () use ($data, $targetArm, $session) {
            foreach ($data['student_ids'] as $studentId) {
                $student = Student::findOrFail($studentId);
                $previousEnrollment = $student->enrollments()->latest('id')->first();

                if ($previousEnrollment) {
                    $previousEnrollment->update(['status' => 'promoted']);
                }

                Enrollment::create([
                    'student_id' => $student->id,
                    'academic_session_id' => $session->id,
                    'school_class_id' => $targetArm->school_class_id,
                    'class_arm_id' => $targetArm->id,
                    'status' => 'active',
                    'promoted_from_enrollment_id' => $previousEnrollment?->id,
                    'promoted_by' => auth()->id(),
                    'promoted_at' => now(),
                ]);

                $student->update(['current_class_arm_id' => $targetArm->id]);
            }
        });

        return redirect()->route('students.promotions')->with('success', count($data['student_ids']).' student(s) promoted to '.$targetArm->full_name.'.');
    }

    public function reversePromotion(Student $student, Enrollment $enrollment)
    {
        $this->authorize('promote', $student);

        abort_unless($enrollment->student_id === $student->id, 404);

        DB::transaction(function () use ($student, $enrollment) {
            $previous = $enrollment->promotedFrom;
            $enrollment->delete();

            if ($previous) {
                $previous->update(['status' => 'active']);
                $student->update(['current_class_arm_id' => $previous->class_arm_id]);
            }
        });

        return back()->with('success', 'Promotion reversed successfully.');
    }

    protected function generateAdmissionNumber(): string
    {
        $year = now()->format('y');
        $count = Student::withTrashed()->count() + 1;

        do {
            $number = "PFA/{$year}/".str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $count++;
        } while (Student::withTrashed()->where('admission_number', $number)->exists());

        return $number;
    }
}
