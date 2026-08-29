<?php

namespace App\Services;

use App\Models\ActivityScore;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;

/**
 * Shared O-Level (UCE) marks persistence for the admin Grades screen AND the
 * teacher portal — one save path so the two can never drift apart.
 *
 * Persists per-activity scores, teacher-entered identifier, EOT score and
 * project work, then stores the COMPUTED final mark (CA + EOT) on the grade
 * row. All grading flows through AssessmentGradingService::computeOLevelFinalMark.
 * Project work keeps its own score/grade and is NEVER merged into the final mark.
 */
class OLevelMarksService
{
    public static function save(Exam $exam, SchoolClass $class, Subject $subject, array $gradesInput, int $userId): int
    {
        $ranges = AssessmentGradingService::rangesForExam($exam, $class);
        $weights = AssessmentGradingService::caWeights();
        $activityMax = AssessmentGradingService::activityMaxScore();
        $projectMax = AssessmentGradingService::projectMaxScore();

        $enrollments = Enrollment::where('school_class_id', $class->id)
            ->where('academic_year_id', $exam->academic_year_id)
            ->where('status', 'active')
            ->get()
            ->keyBy('student_id');

        $saved = 0;

        foreach ($gradesInput as $gradeData) {
            $studentId = (int) ($gradeData['student_id'] ?? 0);
            $enrollment = $enrollments->get($studentId);
            if (! $enrollment) {
                continue;
            }

            // Activities: blank slot = not administered (no row, excluded from the
            // average — never a zero). A cleared previously-scored slot is removed.
            foreach ((array) ($gradeData['activities'] ?? []) as $number => $rawScore) {
                $number = (int) $number;
                if ($number < 1) {
                    continue;
                }
                $slot = [
                    'enrollment_id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'exam_id' => $exam->id,
                    'activity_number' => $number,
                ];
                if ($rawScore === null || $rawScore === '') {
                    ActivityScore::where($slot)->delete();
                    continue;
                }
                $score = (float) $rawScore;
                abort_if($score < 0 || $score > $activityMax, 422, "Activity score {$score} exceeds the maximum of {$activityMax}.");
                ActivityScore::updateOrCreate($slot, [
                    'raw_score' => $score,
                    'status' => 'scored',
                    'recorded_by' => $userId,
                ]);
            }

            $grade = Grade::firstOrNew([
                'exam_id' => $exam->id,
                'student_id' => $studentId,
                'subject_id' => $subject->id,
                'subject_component_id' => null,
            ]);

            $eotRaw = $gradeData['eot_raw_score'] ?? null;
            $eotRaw = ($eotRaw === null || $eotRaw === '') ? null : (float) $eotRaw;
            $eotMax = (float) ($grade->eot_max_score ?? $weights['eot']);
            abort_if($eotRaw !== null && ($eotRaw < 0 || $eotRaw > $eotMax), 422, "EOT score {$eotRaw} exceeds the maximum of {$eotMax}.");

            $eotStatusInput = $gradeData['eot_status'] ?? 'scored';
            // 'scored' only holds with an actual score; a blank score stays pending (null).
            $grade->eot_status = match (true) {
                in_array($eotStatusInput, ['absent', 'withheld'], true) => $eotStatusInput,
                $eotRaw !== null => 'scored',
                default => null,
            };
            $grade->eot_raw_score = $grade->eot_status === 'scored' ? $eotRaw : null;
            $grade->eot_max_score = $eotMax;

            $identifier = $gradeData['identifier'] ?? null;
            $grade->identifier = ($identifier === null || $identifier === '') ? null : (int) $identifier;

            // Project work: own score/grade, never merged into the subject's final mark.
            $projectRaw = $gradeData['project_score_raw'] ?? null;
            $projectRaw = ($projectRaw === null || $projectRaw === '') ? null : (float) $projectRaw;
            $grade->project_score_max = (float) ($grade->project_score_max ?? $projectMax);
            abort_if($projectRaw !== null && ($projectRaw < 0 || $projectRaw > (float) $grade->project_score_max), 422, "Project score {$projectRaw} exceeds the maximum of {$grade->project_score_max}.");
            $grade->project_score_raw = $projectRaw;
            $grade->project_status = $projectRaw !== null ? 'scored' : ($grade->project_status === 'scored' ? null : $grade->project_status);

            $grade->school_class_id = $class->id;
            $grade->remarks = $gradeData['remarks'] ?? $grade->remarks;
            $grade->graded_by = $userId;
            $grade->save();

            // Single grading path: final mark + band computed by the service.
            $result = AssessmentGradingService::computeOLevelFinalMark($enrollment, $subject, $exam, $grade->fresh(), $ranges);
            $grade->refresh();
            $grade->marks_obtained = $result['status'] === AssessmentGradingService::STATUS_GRADED ? $result['final_mark'] : null;
            $grade->grade_letter = $result['grade'];
            $grade->achievement_level = $result['description'];
            $grade->save();
            $saved++;
        }

        return $saved;
    }
}
