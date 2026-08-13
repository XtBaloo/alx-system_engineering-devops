<?php

namespace App\Http\Requests\Concerns;

use App\Models\SchoolSetting;
use App\Models\TimetableEntry;
use Illuminate\Contracts\Validation\Validator;

trait ValidatesTimetableConflicts
{
    /**
     * Reject the entry if the current academic session isn't set, or if the
     * teacher, class, or room is already booked at an overlapping time on the same day.
     */
    protected function withConflictChecks(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $session = SchoolSetting::current()->currentAcademicSession;

            if (! $session) {
                $validator->errors()->add('timetable', 'Please activate an academic session before building a timetable.');

                return;
            }

            $ignoreId = $this->route('timetable')?->id;

            $base = TimetableEntry::query()
                ->where('academic_session_id', $session->id)
                ->overlapping($this->input('day_of_week'), $this->input('start_time'), $this->input('end_time'))
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId));

            if ((clone $base)->where('teacher_id', $this->input('teacher_id'))->exists()) {
                $validator->errors()->add('teacher_id', 'This teacher is already scheduled for another class at this day and time.');
            }

            if ((clone $base)->where('class_arm_id', $this->input('class_arm_id'))->exists()) {
                $validator->errors()->add('class_arm_id', 'This class already has another subject scheduled at this day and time.');
            }

            if ((clone $base)->where('room', $this->input('room'))->exists()) {
                $validator->errors()->add('room', 'This room is already booked for another class at this day and time.');
            }
        });
    }
}
