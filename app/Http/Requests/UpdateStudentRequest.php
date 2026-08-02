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
            'lin' => 'nullable|string|max:30|unique:students,lin,' . $this->route('student')->id,
            'uneb_index_number' => 'nullable|string|max:30',
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
            'previous_school_grade' => 'nullable|string|max:100',
            'previous_school_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'admission_date' => 'nullable|date',
            'status' => 'nullable|in:active,graduated,transferred,withdrawn,suspended',
            'boarding_status' => 'nullable|in:day,boarding',
            'photo' => 'nullable|image|max:2048',
            'subject_combination_id' => 'nullable|exists:subject_combinations,id',
        ];
    }
}
