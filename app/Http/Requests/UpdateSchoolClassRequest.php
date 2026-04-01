<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'level' => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ];
    }
}
