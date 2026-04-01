<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:50',
            'publisher' => 'nullable|string|max:255',
            'publish_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'book_category_id' => 'nullable|exists:book_categories,id',
            'total_copies' => 'required|integer|min:1|max:10000',
            'shelf_location' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
        ];
    }
}
