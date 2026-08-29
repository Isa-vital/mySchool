<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveGradesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // O-Level activity/CA layer caps (hard stops also enforced in the models).
        $activityMax = \App\Services\AssessmentGradingService::activityMaxScore();
        $eotMax = \App\Services\AssessmentGradingService::caWeights()['eot'];
        $projectMax = \App\Services\AssessmentGradingService::projectMaxScore();

        return [
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'grades' => 'required|array|min:1',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.marks_obtained' => 'nullable|numeric|min:0|max:500',
            'grades.*.remarks' => 'nullable|string|max:500',
            'olevel_entry' => 'nullable|boolean',
            'grades.*.activities' => 'nullable|array',
            'grades.*.activities.*' => "nullable|numeric|min:0|max:{$activityMax}",
            'grades.*.identifier' => 'nullable|integer|in:1,2,3',
            'grades.*.eot_raw_score' => "nullable|numeric|min:0|max:{$eotMax}",
            'grades.*.eot_status' => 'nullable|in:scored,absent,withheld',
            'grades.*.project_score_raw' => "nullable|numeric|min:0|max:{$projectMax}",
        ];
    }
}
