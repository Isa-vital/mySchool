<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            // CHANGED: 'auto' resolves format from each student's class (P.1-P.7 primary, S.1-S.4 o-level, S.5-S.6 a-level)
            // 'assessment_format' => 'nullable|in:primary,o-level,a-level',
            'assessment_format' => 'nullable|in:auto,primary,o-level,a-level',
            'max_points' => 'nullable|integer|min:1|max:500',
            'is_report_card' => 'nullable|boolean',
            'grading_scale_id' => 'nullable|exists:grading_scales,id',
            'report_components' => 'nullable|array',
            'report_components.*.exam_id' => 'nullable|exists:exams,id|distinct',
            'report_components.*.weight' => 'nullable|numeric|min:0|max:1000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:2000',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:school_classes,id',
        ];
    }
}
