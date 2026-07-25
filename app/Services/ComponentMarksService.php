<?php

namespace App\Services;

// CHANGED (A6): shared load/save logic for subjects assessed in multiple weighted
// components (Paper 1/2, theory + practical). Used by both the admin grades flow
// and the teacher portal so the two entry paths cannot diverge.

use App\Models\Exam;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Support\Collection;

class ComponentMarksService
{
    /**
     * Existing component marks keyed "{student_id}:{component_id}".
     */
    public static function existingMarks(Exam $exam, int $classId, Subject $subject): Collection
    {
        return Grade::where('exam_id', $exam->id)
            ->where('school_class_id', $classId)
            ->where('subject_id', $subject->id)
            ->whereNotNull('subject_component_id')
            ->get()
            ->keyBy(fn($g) => $g->student_id . ':' . $g->subject_component_id);
    }

    /**
     * Persist raw component scores. Grade letters are NOT stored per component —
     * the combined subject grade is computed on read from raw scores + grading rules.
     *
     * @param array $marks [student_id => [component_id => raw score]]
     */
    public static function saveMarks(Exam $exam, int $classId, Subject $subject, array $marks, int $userId): int
    {
        $components = $subject->components()->get()->keyBy('id');
        $saved = 0;

        foreach ($marks as $studentId => $componentScores) {
            if (! is_array($componentScores)) {
                continue;
            }

            foreach ($componentScores as $componentId => $score) {
                if ($score === null || $score === '') {
                    continue;
                }

                $component = $components->get((int) $componentId);
                if (! $component) {
                    continue; // component doesn't belong to this subject
                }

                $score = max(0.0, min((float) $score, (float) $component->max_score));

                Grade::updateOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'student_id' => (int) $studentId,
                        'subject_id' => $subject->id,
                        'subject_component_id' => $component->id,
                    ],
                    [
                        'school_class_id' => $classId,
                        'marks_obtained' => $score,
                        'graded_by' => $userId,
                    ]
                );
                $saved++;
            }
        }

        return $saved;
    }
}
