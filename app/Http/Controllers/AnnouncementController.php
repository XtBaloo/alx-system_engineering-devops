<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->hasRole('teacher') ? 'teachers' : ($user->hasRole('student') ? 'students' : ($user->hasRole('parent') ? 'parents' : null));
        $classId = $user->hasRole('student') ? $user->student?->currentClassArm?->school_class_id : null;

        $announcements = Announcement::with('author', 'schoolClass')
            ->published()
            ->when(! $user->can('manage-announcements'), fn ($q) => $q->forAudience($role ?? 'everyone', $classId))
            ->latest('published_at')
            ->paginate(15);

        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        $this->authorize('create', Announcement::class);

        $classes = SchoolClass::ordered()->get();

        return view('announcements.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Announcement::class);

        $data = $this->validated($request);
        $data['author_id'] = $request->user()->id;

        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        Announcement::create($data);

        return redirect()->route('announcements.index')->with('success', 'Announcement created.');
    }

    public function edit(Announcement $announcement)
    {
        $this->authorize('update', $announcement);

        $classes = SchoolClass::ordered()->get();

        return view('announcements.edit', compact('announcement', 'classes'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorize('update', $announcement);

        $data = $this->validated($request);

        if ($data['status'] === 'published' && $announcement->status !== 'published') {
            $data['published_at'] = now();
        }

        $announcement->update($data);

        return redirect()->route('announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorize('delete', $announcement);

        $announcement->delete();

        return back()->with('success', 'Announcement removed.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'target' => ['required', 'in:everyone,teachers,students,parents,class'],
            'school_class_id' => ['nullable', 'required_if:target,class', 'exists:classes,id'],
            'status' => ['required', 'in:draft,published'],
        ]);
    }
}
