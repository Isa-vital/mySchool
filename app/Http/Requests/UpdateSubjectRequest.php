<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectRequest extends FormRequest
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
            'type' => 'nullable|in:core,elective',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            // CHANGED (UACE paper rebuild): A-Level paper structure + category
            'paper_count' => 'nullable|integer|min:2|max:4',
            'subject_category' => 'nullable|in:science,non_science',
            'is_subsidiary' => 'nullable|boolean',
        ];
    }
}
