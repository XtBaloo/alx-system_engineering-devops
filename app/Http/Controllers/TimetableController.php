<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimetableEntryRequest;
use App\Http\Requests\UpdateTimetableEntryRequest;
use App\Models\ClassArm;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $classArms = ClassArm::with('schoolClass')->orderBy('school_class_id')->orderBy('name')->get();
        $session = SchoolSetting::current()->currentAcademicSession;

        $classArmId = $request->input('class_arm_id', $classArms->first()?->id);

        $entries = collect();

        if ($session && $classArmId) {
            $entries = TimetableEntry::with(['subject', 'teacher'])
                ->where('academic_session_id', $session->id)
                ->where('class_arm_id', $classArmId)
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week');
        }

        $days = TimetableEntry::DAYS;

        return view('timetable.index', compact('classArms', 'classArmId', 'entries', 'days', 'session'));
    }

    public function create()
    {
        return view('timetable.create', $this->formOptions());
    }

    public function store(StoreTimetableEntryRequest $request)
    {
        $session = SchoolSetting::current()->currentAcademicSession;

        TimetableEntry::create($request->validated() + ['academic_session_id' => $session->id]);

        return redirect()->route('timetable.index', ['class_arm_id' => $request->class_arm_id])
            ->with('success', 'Timetable entry added.');
    }

    public function edit(TimetableEntry $timetable)
    {
        return view('timetable.edit', ['timetable' => $timetable] + $this->formOptions());
    }

    public function update(UpdateTimetableEntryRequest $request, TimetableEntry $timetable)
    {
        $timetable->update($request->validated());

        return redirect()->route('timetable.index', ['class_arm_id' => $timetable->class_arm_id])
            ->with('success', 'Timetable entry updated.');
    }

    public function destroy(TimetableEntry $timetable)
    {
        $classArmId = $timetable->class_arm_id;
        $timetable->delete();

        return redirect()->route('timetable.index', ['class_arm_id' => $classArmId])
            ->with('success', 'Timetable entry removed.');
    }

    public function mine(Request $request)
    {
        $teacher = $request->user()->teacher;

        abort_unless($teacher, 404, 'No teacher profile is linked to your account.');

        $session = SchoolSetting::current()->currentAcademicSession;
        $entries = collect();

        if ($session) {
            $entries = TimetableEntry::with(['classArm.schoolClass', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->where('academic_session_id', $session->id)
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week');
        }

        $days = TimetableEntry::DAYS;

        return view('timetable.mine', compact('entries', 'days', 'session'));
    }

    protected function formOptions(): array
    {
        return [
            'classArms' => ClassArm::with('schoolClass')->orderBy('school_class_id')->orderBy('name')->get(),
            'subjects' => Subject::active()->orderBy('name')->get(),
            'teachers' => Teacher::active()->orderBy('first_name')->get(),
            'days' => TimetableEntry::DAYS,
        ];
    }
}
