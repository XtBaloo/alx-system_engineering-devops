<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    public function select(Request $request)
    {
        $students = Student::active()->orderBy('first_name')
            ->when($request->search, function ($q, $search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%");
            })
            ->paginate(20)->withQueryString();

        return view('report-cards.select', compact('students'));
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student);

        $data = $this->buildReportCardData($student);

        return view('report-cards.show', $data);
    }

    public function pdf(Student $student)
    {
        $this->authorize('view', $student);

        $data = $this->buildReportCardData($student);

        $pdf = Pdf::loadView('report-cards.pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download("Report-Card-{$student->admission_number}.pdf");
    }

    protected function buildReportCardData(Student $student): array
    {
        $settings = SchoolSetting::current();
        $term = $settings->currentTerm;

        $results = $student->results()
            ->with('subject')
            ->where('status', 'published')
            ->when($term, fn ($q) => $q->where('term_id', $term->id))
            ->get()
            ->sortBy(fn ($r) => $r->subject?->name);

        $attendance = $student->attendances()
            ->when($term, fn ($q) => $q->where('term_id', $term->id))
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $totalDays = $attendance->sum();
        $classSize = $results->first()?->subject_class_size;

        $average = $results->count() ? round($results->avg('total_score'), 2) : 0;

        return compact('student', 'settings', 'term', 'results', 'attendance', 'totalDays', 'classSize', 'average');
    }
}
