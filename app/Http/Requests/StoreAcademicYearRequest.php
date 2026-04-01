<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'nullable|boolean',
            'terms' => 'nullable|array|max:10',
            'terms.*.name' => 'required_with:terms|string|max:255',
            'terms.*.start_date' => 'required_with:terms|date',
            'terms.*.end_date' => 'required_with:terms|date',
        ];
    }
}
