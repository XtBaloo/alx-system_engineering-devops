<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::query()
            ->when($request->search, fn ($q, $search) => $q->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
            }))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('subjects.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Subject::create($data);

        return redirect()->route('subjects.index')->with('success', 'Subject created.');
    }

    public function edit(Subject $subject)
    {
        return view('subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $data = $this->validated($request, $subject);
        $subject->update($data);

        return redirect()->route('subjects.index')->with('success', 'Subject updated.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return back()->with('success', 'Subject archived.');
    }

    protected function validated(Request $request, ?Subject $subject = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code,'.($subject?->id)],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $data['is_compulsory'] = $request->boolean('is_compulsory');

        return $data;
    }
}
