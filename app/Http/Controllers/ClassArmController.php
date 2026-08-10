<?php

namespace App\Http\Controllers;

use App\Models\ClassArm;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ClassArmController extends Controller
{
    public function index()
    {
        $classArms = ClassArm::with('schoolClass', 'classTeacher')->withCount('students')
            ->join('classes', 'classes.id', '=', 'class_arms.school_class_id')
            ->orderBy('classes.order')->orderBy('class_arms.name')
            ->select('class_arms.*')
            ->paginate(20);

        return view('class-arms.index', compact('classArms'));
    }

    public function create()
    {
        $classes = SchoolClass::ordered()->get();
        $teachers = Teacher::active()->orderBy('first_name')->get();

        return view('class-arms.create', compact('classes', 'teachers'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        ClassArm::create($data);

        return redirect()->route('class-arms.index')->with('success', 'Class arm created.');
    }

    public function edit(ClassArm $classArm)
    {
        $classes = SchoolClass::ordered()->get();
        $teachers = Teacher::active()->orderBy('first_name')->get();

        return view('class-arms.edit', compact('classArm', 'classes', 'teachers'));
    }

    public function update(Request $request, ClassArm $classArm)
    {
        $data = $this->validated($request, $classArm);
        $classArm->update($data);

        return redirect()->route('class-arms.index')->with('success', 'Class arm updated.');
    }

    public function destroy(ClassArm $classArm)
    {
        if ($classArm->students()->exists()) {
            return back()->with('error', 'Cannot delete a class arm that has students.');
        }

        $classArm->delete();

        return back()->with('success', 'Class arm removed.');
    }

    protected function validated(Request $request, ?ClassArm $classArm = null): array
    {
        return $request->validate([
            'school_class_id' => ['required', 'exists:classes,id'],
            'name' => ['required', 'string', 'max:10'],
            'class_teacher_id' => ['nullable', 'exists:teachers,id'],
            'capacity' => ['nullable', 'integer', 'min:1'],
        ]);
    }
}
