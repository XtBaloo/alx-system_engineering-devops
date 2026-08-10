<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::withCount('classArms')->ordered()->paginate(20);

        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        $subjects = Subject::active()->orderBy('name')->get();

        return view('classes.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $class = SchoolClass::create($data);
        $class->subjects()->sync($request->input('subjects', []));

        return redirect()->route('classes.index')->with('success', 'Class created.');
    }

    public function edit(SchoolClass $class)
    {
        $subjects = Subject::active()->orderBy('name')->get();
        $class->load('subjects', 'classArms');

        return view('classes.edit', ['schoolClass' => $class, 'subjects' => $subjects]);
    }

    public function update(Request $request, SchoolClass $class)
    {
        $data = $this->validated($request, $class);
        $class->update($data);
        $class->subjects()->sync($request->input('subjects', []));

        return redirect()->route('classes.index')->with('success', 'Class updated.');
    }

    public function destroy(SchoolClass $class)
    {
        if ($class->classArms()->exists()) {
            return back()->with('error', 'Remove class arms before deleting this class.');
        }

        $class->delete();

        return back()->with('success', 'Class removed.');
    }

    protected function validated(Request $request, ?SchoolClass $class = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:classes,name,'.($class?->id)],
            'level' => ['required', 'in:nursery,primary,junior_secondary,senior_secondary'],
            'order' => ['required', 'integer', 'min:0'],
        ]);
    }
}
