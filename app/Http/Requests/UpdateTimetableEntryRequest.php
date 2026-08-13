<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesTimetableConflicts;
use App\Models\TimetableEntry;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimetableEntryRequest extends FormRequest
{
    use ValidatesTimetableConflicts;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_arm_id' => ['required', 'exists:class_arms,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'day_of_week' => ['required', 'in:'.implode(',', array_keys(TimetableEntry::DAYS))],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->withConflictChecks($validator);
    }
}
