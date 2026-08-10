<?php

namespace App\Http\Controllers;

use App\Models\AssessmentType;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;

class AssessmentTypeController extends Controller
{
    public function index()
    {
        $types = AssessmentType::orderBy('order')->get();
        $totalConfigured = $types->sum('max_score') + SchoolSetting::current()->examination_max_score;

        return view('assessment-types.index', compact('types', 'totalConfigured'));
    }

    public function create()
    {
        return view('assessment-types.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        AssessmentType::create($data);

        return redirect()->route('assessment-types.index')->with('success', 'Assessment component added.');
    }

    public function edit(AssessmentType $assessmentType)
    {
        return view('assessment-types.edit', compact('assessmentType'));
    }

    public function update(Request $request, AssessmentType $assessmentType)
    {
        $data = $this->validated($request);
        $assessmentType->update($data);

        return redirect()->route('assessment-types.index')->with('success', 'Assessment component updated.');
    }

    public function destroy(AssessmentType $assessmentType)
    {
        if ($assessmentType->assessmentScores()->exists()) {
            return back()->with('error', 'Cannot remove a component that already has recorded scores.');
        }

        $assessmentType->delete();

        return back()->with('success', 'Assessment component removed.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20'],
            'max_score' => ['required', 'integer', 'min:1', 'max:100'],
            'order' => ['required', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
