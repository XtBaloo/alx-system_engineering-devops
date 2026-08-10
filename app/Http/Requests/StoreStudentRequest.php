<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Student::class);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'state_of_origin' => ['nullable', 'string', 'max:255'],
            'lga' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'admission_date' => ['required', 'date'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'current_class_arm_id' => ['required', 'exists:class_arms,id'],
            'status' => ['required', 'in:active,graduated,withdrawn,suspended,transferred,archived'],
            'create_login' => ['boolean'],

            'guardian_id' => ['nullable', 'exists:guardians,id'],
            'guardian_relationship' => ['required_with:guardian_id,new_guardian_first_name', 'nullable', 'string', 'max:100'],
            'new_guardian_first_name' => ['nullable', 'string', 'max:255'],
            'new_guardian_last_name' => ['nullable', 'string', 'max:255', 'required_with:new_guardian_first_name'],
            'new_guardian_phone' => ['nullable', 'string', 'max:50'],
            'new_guardian_email' => ['nullable', 'email', 'max:255'],
            'new_guardian_occupation' => ['nullable', 'string', 'max:255'],
        ];
    }
}
