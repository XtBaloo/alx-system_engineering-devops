<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicSessionController extends Controller
{
    public function index()
    {
        $sessions = AcademicSession::withCount('terms')->orderByDesc('start_date')->paginate(15);

        return view('academic-sessions.index', compact('sessions'));
    }

    public function create()
    {
        return view('academic-sessions.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        AcademicSession::create($data);

        return redirect()->route('academic-sessions.index')->with('success', 'Academic session created.');
    }

    public function edit(AcademicSession $academicSession)
    {
        return view('academic-sessions.edit', compact('academicSession'));
    }

    public function update(Request $request, AcademicSession $academicSession)
    {
        $data = $this->validated($request, $academicSession);
        $academicSession->update($data);

        return redirect()->route('academic-sessions.index')->with('success', 'Academic session updated.');
    }

    public function destroy(AcademicSession $academicSession)
    {
        if ($academicSession->enrollments()->exists()) {
            return back()->with('error', 'Cannot delete a session that already has student records.');
        }

        $academicSession->delete();

        return back()->with('success', 'Academic session removed.');
    }

    public function activate(AcademicSession $academicSession)
    {
        DB::transaction(function () use ($academicSession) {
            AcademicSession::where('id', '!=', $academicSession->id)->update(['is_active' => false]);
            $academicSession->update(['is_active' => true]);

            $settings = SchoolSetting::current();
            $settings->update(['current_academic_session_id' => $academicSession->id, 'current_term_id' => null]);
        });

        return back()->with('success', "{$academicSession->name} is now the active academic session.");
    }

    protected function validated(Request $request, ?AcademicSession $session = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:academic_sessions,name,'.($session?->id)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);
    }
}
