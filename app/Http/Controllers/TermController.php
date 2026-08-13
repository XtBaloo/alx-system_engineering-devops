<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\SchoolSetting;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TermController extends Controller
{
    public function index()
    {
        $terms = Term::with('academicSession')->orderByDesc('start_date')->paginate(15);

        return view('terms.index', compact('terms'));
    }

    public function create()
    {
        $sessions = AcademicSession::orderByDesc('start_date')->get();

        return view('terms.create', compact('sessions'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Term::create($data);

        return redirect()->route('terms.index')->with('success', 'Term created.');
    }

    public function edit(Term $term)
    {
        $sessions = AcademicSession::orderByDesc('start_date')->get();

        return view('terms.edit', compact('term', 'sessions'));
    }

    public function update(Request $request, Term $term)
    {
        if (! $term->isOpen()) {
            return back()->with('error', 'This term is closed and cannot be edited. Reopen it first.');
        }

        $data = $this->validated($request);
        $term->update($data);

        return redirect()->route('terms.index')->with('success', 'Term updated.');
    }

    public function destroy(Term $term)
    {
        if (\App\Models\Attendance::where('term_id', $term->id)->exists() || \App\Models\Result::where('term_id', $term->id)->exists()) {
            return back()->with('error', 'Cannot delete a term that already has academic records.');
        }

        $term->delete();

        return back()->with('success', 'Term removed.');
    }

    public function activate(Term $term)
    {
        DB::transaction(function () use ($term) {
            Term::query()->update(['is_active' => false]);
            $term->update(['is_active' => true]);

            $settings = SchoolSetting::current();
            $settings->update([
                'current_term_id' => $term->id,
                'current_academic_session_id' => $term->academic_session_id,
            ]);
        });

        return back()->with('success', "{$term->name} is now the active term.");
    }

    public function close(Term $term)
    {
        $term->update(['status' => 'closed', 'closed_at' => now(), 'closed_by' => auth()->id()]);

        return back()->with('success', "{$term->name} has been closed.");
    }

    public function reopen(Term $term)
    {
        $term->update(['status' => 'open', 'closed_at' => null, 'closed_by' => null]);

        return back()->with('success', "{$term->name} has been reopened.");
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'name' => ['required', 'in:First Term,Second Term,Third Term'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);
    }
}
