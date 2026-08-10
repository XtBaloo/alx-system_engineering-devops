<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Teacher::class);

        $teachers = Teacher::query()
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('teacher_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('first_name')->paginate(20)->withQueryString();

        return view('teachers.index', compact('teachers'));
    }

    public function create()
    {
        $this->authorize('create', Teacher::class);

        return view('teachers.create');
    }

    public function store(StoreTeacherRequest $request)
    {
        $data = $request->validated();
        $createLogin = $request->boolean('create_login');
        unset($data['create_login']);

        $data['teacher_id'] = $this->generateTeacherId();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('teachers', 'public');
        }

        $teacher = DB::transaction(function () use ($data, $createLogin) {
            if ($createLogin && ! empty($data['email'])) {
                $user = User::create([
                    'name' => trim($data['first_name'].' '.$data['last_name']),
                    'email' => $data['email'],
                    'password' => Hash::make(Str::random(12)),
                ]);
                $user->assignRole('teacher');
                $data['user_id'] = $user->id;
            }

            return Teacher::create($data);
        });

        return redirect()->route('teachers.show', $teacher)->with('success', 'Teacher added successfully.');
    }

    public function show(Teacher $teacher)
    {
        $this->authorize('view', $teacher);

        $teacher->load(['teacherAssignments.classArm.schoolClass', 'teacherAssignments.subject']);

        return view('teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        $this->authorize('update', $teacher);

        return view('teachers.edit', compact('teacher'));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('teachers', 'public');
        }

        $teacher->update($data);

        return redirect()->route('teachers.show', $teacher)->with('success', 'Teacher updated successfully.');
    }

    public function destroy(Teacher $teacher)
    {
        $this->authorize('delete', $teacher);

        $teacher->delete();

        return redirect()->route('teachers.index')->with('success', 'Teacher removed.');
    }

    protected function generateTeacherId(): string
    {
        $year = now()->format('y');
        $count = Teacher::withTrashed()->count() + 1;

        do {
            $id = "PFA/TCH/{$year}/".str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $count++;
        } while (Teacher::withTrashed()->where('teacher_id', $id)->exists());

        return $id;
    }
}
