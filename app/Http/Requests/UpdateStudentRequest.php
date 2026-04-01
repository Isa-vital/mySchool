<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'other_names' => 'nullable|string|max:255',
            'admission_number' => 'required|string|unique:students,admission_number,' . $this->route('student')->id,
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date|before:today',
            'nationality' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'blood_group' => 'nullable|string|max:5',
            'medical_conditions' => 'nullable|string|max:2000',
            'previous_school' => 'nullable|string|max:255',
            'admission_date' => 'nullable|date',
            'status' => 'nullable|in:active,graduated,transferred,withdrawn,suspended',
            'photo' => 'nullable|image|max:2048',
        ];
    }
}
