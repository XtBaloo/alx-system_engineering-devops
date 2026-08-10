<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('teacher'));
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'employment_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,on_leave,resigned,terminated'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
