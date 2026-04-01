<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
            'target_audience' => 'required|in:all,staff,students,parents,specific_class',
            'school_class_id' => 'nullable|required_if:target_audience,specific_class|exists:school_classes,id',
            'publish_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:publish_date',
            'is_published' => 'nullable|boolean',
        ];
    }
}
