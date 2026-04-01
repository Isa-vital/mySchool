<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_number' => 'required|string|max:50|unique:staff,staff_number,' . $this->route('staff')->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'designation' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'join_date' => 'nullable|date',
            'employment_type' => 'nullable|in:full-time,part-time,contract',
            'photo' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ];
    }
}
