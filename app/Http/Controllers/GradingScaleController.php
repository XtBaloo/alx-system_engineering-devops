<?php

namespace App\Http\Controllers;

use App\Models\GradingScale;
use Illuminate\Http\Request;

class GradingScaleController extends Controller
{
    public function index()
    {
        $scales = GradingScale::orderBy('order')->get();

        return view('grading-scales.index', compact('scales'));
    }

    public function create()
    {
        return view('grading-scales.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        GradingScale::create($data);

        return redirect()->route('grading-scales.index')->with('success', 'Grading scale added.');
    }

    public function edit(GradingScale $gradingScale)
    {
        return view('grading-scales.edit', compact('gradingScale'));
    }

    public function update(Request $request, GradingScale $gradingScale)
    {
        $data = $this->validated($request);
        $gradingScale->update($data);

        return redirect()->route('grading-scales.index')->with('success', 'Grading scale updated.');
    }

    public function destroy(GradingScale $gradingScale)
    {
        $gradingScale->delete();

        return back()->with('success', 'Grading scale removed.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'min_score' => ['required', 'integer', 'min:0', 'max:100'],
            'max_score' => ['required', 'integer', 'min:0', 'max:100', 'gte:min_score'],
            'grade' => ['required', 'string', 'max:5'],
            'remark' => ['required', 'string', 'max:255'],
            'grade_point' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'order' => ['required', 'integer', 'min:0'],
        ]);
    }
}
