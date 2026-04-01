<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolClassRequest extends FormRequest
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
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
            'sections' => 'nullable|array|max:20',
            'sections.*.name' => 'required_with:sections|string|max:255',
            'sections.*.capacity' => 'nullable|integer|min:1|max:500',
        ];
    }
}
