<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1|max:50',
            'items.*.fee_type_id' => 'required|exists:fee_types,id',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.amount' => 'required|numeric|min:0|max:99999999.99',
        ];
    }
}
