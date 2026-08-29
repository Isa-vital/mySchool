<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\SchoolClass;
use Illuminate\Support\Collection;

class ReportCardFormatter
{
    /**
     * Format report data based on assessment format.
     * CHANGED: accepts the student's class so 'auto' format resolves per class
     * (P.1-P.7 => primary, S.1-S.4 => o-level, S.5-S.6 => a-level).
     */
    public static function format(
        Exam $exam,
        Collection $grades,
        float $totalMarks,
        float $average,
        ?int $position,
        ?int $classSize,
        ?SchoolClass $schoolClass = null,
        $enrollment = null, // CHANGED (A-Level rebuild): carries the student's combination
        ?array $uace = null // CHANGED (UACE paper rebuild): paper-level engine result, when it exists
    ): array {
        // CHANGED: was `match ($exam->assessment_format)` — now resolves 'auto' via the class.
        return match (AssessmentGradingService::resolveFormat($exam->assessment_format, $schoolClass)) {
            'primary' => self::formatPrimary($grades, $totalMarks, $average, $position, $classSize, $exam, $schoolClass),
            // enrollment carries the activity/CA layer for o-level
            'o-level' => self::formatOLevel($grades, $totalMarks, $average, $position, $classSize, $exam, $schoolClass, $enrollment),
            'a-level' => self::formatALevel($grades, $totalMarks, $average, $position, $classSize, $exam, $schoolClass, $enrollment, $uace),
            default => self::formatPrimary($grades, $totalMarks, $average, $position, $classSize, $exam, $schoolClass),
        };
    }

    /**
     * PRIMARY FORMAT: Traditional marks + achievement levels
     * Shows term breakdown (BOT/MID/END) when multiple component exams exist.
     * Each subject shows: Marks (0-100) per term → Achievement Level
     */
    private static function formatPrimary(
        Collection $grades,
        float $totalMarks,
        float $average,
        ?int $position,
        ?int $classSize,
        Exam $exam,
        ?SchoolClass $schoolClass = null // CHANGED: class-aware ranges for 'auto' format
    ): array {
        $ranges = AssessmentGradingService::rangesForExam($exam, $schoolClass);

        $gradedSubjects = $grades->map(function ($grade) use ($ranges) {
            $marks = $grade->marks_obtained ?? 0;
            $resolved = AssessmentGradingService::resolve($marks, $ranges);

            // Include component breakdown for term-by-term display
            $components = [];
            if (is_object($grade) && isset($grade->components) && is_array($grade->components)) {
                $components = collect($grade->components)
                    ->map(fn($comp) => [
                        'exam_name' => $comp['exam_name'] ?? $comp['name'] ?? '',
                        'marks' => (float) $comp['marks'],
                        'full_marks' => (float) $comp['full_marks'],
                        'percentage' => (float) ($comp['marks'] / $comp['full_marks']) * 100,
                    ])
                    ->sortBy('exam_name')
                    ->values()
                    ->all();
            }

            return [
                'subject' => $grade->subject->name,
                'marks' => $marks,
                'grade' => $resolved['grade'],
                'color' => self::getAchievementLevelColor($resolved['grade']),
                'components' => $components,
            ];
        });

        $overallLevel = AssessmentGradingService::resolve($average, $ranges)['grade'];

        return [
            'format' => 'primary',
            'format_label' => 'School Report (Primary)',
            'subjects' => $gradedSubjects,
            'total_marks' => $totalMarks,
            'average' => round($average, 2),
            'overall_grade' => $overallLevel,
            'position' => $position,
            'class_size' => $classSize,
            'remarks' => [
                'conduct_required' => true,
                'teacher_comment_required' => true,
                'head_comment_required' => false,
            ],
        ];
    }

    /**
     * O-LEVEL FORMAT (new curriculum): Competency-based scoring.
     * Every subject routes through AssessmentGradingService::computeOLevelFinalMark
     * (single grading path: activity/CA layer, legacy single-mark rows, and null
     * marks all resolve there). Shows grade (A-E) + points (4-0, higher is better),
     * CA/EOT breakdown, teacher-entered identifier and explicit INCOMPLETE /
     * NOT YET ASSESSED states. Composite reports aggregate component exams.
     */
    private static function formatOLevel(
        Collection $grades,
        float $totalMarks,
        float $average,
        ?int $position,
        ?int $classSize,
        Exam $exam,
        ?SchoolClass $schoolClass = null,
        $enrollment = null // carries the activity/CA layer context
    ): array {
        $ranges = AssessmentGradingService::rangesForExam($exam, $schoolClass);
        $weights = AssessmentGradingService::caWeights();
        $activityMax = AssessmentGradingService::activityMaxScore();

        $gradedSubjects = $grades->map(function ($grade) use ($ranges, $exam, $enrollment, $weights, $activityMax) {
            // Component breakdown for composite (multi-exam) reports.
            $components = [];
            if (is_object($grade) && isset($grade->components) && is_array($grade->components)) {
                $components = collect($grade->components)
                    ->map(function ($comp) use ($ranges) {
                        $compResult = AssessmentGradingService::resolve((float) $comp['marks'], $ranges);
                        return [
                            'exam_name' => $comp['exam_name'] ?? $comp['name'] ?? '',
                            'marks' => (float) $comp['marks'],
                            'full_marks' => (float) $comp['full_marks'],
                            'percentage' => round((float) ($comp['marks'] / $comp['full_marks']) * 100, 1),
                            'grade' => $compResult['grade'],
                            'points' => $compResult['points'],
                            'descriptor' => $compResult['description'],
                        ];
                    })
                    ->sortBy('exam_name')
                    ->values()
                    ->all();
            }

            $isGradeRow = $grade instanceof \App\Models\Grade;

            if ($isGradeRow && $enrollment && $grade->subject) {
                // Direct Grade rows: full activity/CA computation (also covers legacy
                // single-mark rows and null marks in the same method).
                $result = AssessmentGradingService::computeOLevelFinalMark($enrollment, $grade->subject, $exam, $grade, $ranges);
            } else {
                // Composite rows are pre-blended percentages; band null-safely.
                $marks = $grade->marks_obtained !== null ? (float) $grade->marks_obtained : null;
                $result = AssessmentGradingService::resolveMark($marks, $ranges) + [
                    'activity_scores' => [],
                    'activity_avg' => null,
                    'activity_max' => $activityMax,
                    'ca_mark' => null,
                    'ca_total' => $weights['ca'],
                    'eot_raw_score' => null,
                    'eot_max_score' => $weights['eot'],
                    'eot_total' => $weights['eot'],
                    'eot_status' => null,
                    'identifier' => $isGradeRow ? $grade->identifier : null,
                    'project_score_raw' => null,
                    'project_score_max' => AssessmentGradingService::projectMaxScore(),
                    'project_status' => null,
                    'project_grade' => null,
                    'final_mark' => $marks,
                ];
            }

            return [
                'subject' => $grade->subject->name,
                'status' => $result['status'],
                'raw_marks' => $result['final_mark'] ?? 0,
                'final_mark' => $result['final_mark'],
                'activities' => $result['activity_scores'],
                'activity_avg' => $result['activity_avg'],
                'activity_max' => $result['activity_max'],
                'ca_mark' => $result['ca_mark'],
                'ca_total' => $result['ca_total'],
                'eot_raw_score' => $result['eot_raw_score'],
                'eot_max_score' => $result['eot_max_score'],
                'eot_status' => $result['eot_status'],
                'identifier' => $result['identifier'],
                'project_score_raw' => $result['project_score_raw'],
                'project_score_max' => $result['project_score_max'],
                'project_status' => $result['project_status'],
                'project_grade' => $result['project_grade'],
                'grade' => $result['grade'],
                'points' => $result['points'],
                'descriptor' => $result['description'],
                'color' => $result['grade'] !== null ? self::getOLevelGradeColor($result['grade']) : 'bg-amber-100 text-amber-800',
                'components' => $components,
            ];
        });

        // Total Points: ONLY subjects with a resolved grade count — INCOMPLETE and
        // NOT YET ASSESSED subjects are excluded from numerator AND denominator.
        $resolved = $gradedSubjects->where('status', AssessmentGradingService::STATUS_GRADED);
        $resolvedCount = $resolved->count();
        $maxPointsValue = AssessmentGradingService::maxPointsForRanges($ranges);
        $totalPoints = (int) $resolved->sum('points');
        $maxTotalPoints = (int) ($resolvedCount * $maxPointsValue);
        $subjectCount = $gradedSubjects->count();
        $averagePoints = $resolvedCount > 0 ? round($totalPoints / $resolvedCount, 2) : 0;

        // ONE overall-grade formula: average percentage of graded subjects, banded by
        // the same configurable scale each subject uses (UgandaGrading::oLevelOverallLevel
        // delegates to this same source — the two can no longer disagree).
        $gradedAverage = $resolvedCount > 0 ? round($resolved->sum('final_mark') / $resolvedCount, 2) : null;
        $overall = AssessmentGradingService::resolveMark($gradedAverage, $ranges);

        return [
            'format' => 'o-level',
            'format_label' => 'O-Level School Report (Competency-Based)',
            'subjects' => $gradedSubjects,
            'total_marks' => $totalMarks,
            'average' => $gradedAverage ?? round($average, 2),
            'total_points' => $totalPoints,
            'max_total_points' => $maxTotalPoints,
            'max_points_per_subject' => (int) $maxPointsValue,
            'resolved_subject_count' => $resolvedCount,
            'points_denominator_label' => "{$totalPoints} / ({$resolvedCount} subjects \u{00d7} " . (int) $maxPointsValue . ')',
            'average_points' => $averagePoints,
            'subject_count' => $subjectCount,
            'overall_grade' => $overall['grade'] ?? $overall['description'],
            'overall_descriptor' => $overall['description'],
            'ca_total' => $weights['ca'],
            'eot_total' => $weights['eot'],
            'position' => $position,
            'class_size' => $classSize,
            'remarks' => [
                'conduct_required' => true,
                'teacher_comment_required' => true,
                'head_comment_required' => true,
            ],
        ];
    }

    /**
     * A-LEVEL FORMAT (UACE): combination-based.
     * Principals: A-F => 6,5,4,3,2,1(O),0 points. Subsidiaries: pass = 1 point.
     * Total out of 20 (3 principals x 6 + 2 subsidiaries x 1).
     */
    private static function formatALevel(
        Collection $grades,
        float $totalMarks,
        float $average,
        ?int $position,
        ?int $classSize,
        Exam $exam,
        ?SchoolClass $schoolClass = null,
        $enrollment = null,
        ?array $uace = null // CHANGED (UACE paper rebuild)
    ): array {
        // CHANGED (UACE paper rebuild): when per-paper results exist, the paper-level
        // engine is authoritative. One row per PAPER grouped under the subject;
        // INCOMPLETE/UNMATCHED are printed explicitly — never a blank or a guess.
        if ($uace) {
            return [
                'format' => 'a-level',
                'format_label' => 'A-Level School Report (UACE)',
                'paper_based' => true,
                'cycle' => $uace['cycle'],
                'sitting' => $uace['sitting'],
                'subjects' => collect($uace['principals']),
                'subsidiaries' => collect($uace['subsidiaries']),
                'excluded_subjects' => [],
                'combination_code' => $uace['combination_code'],
                'combination_name' => $uace['combination_name'],
                'has_combination' => true,
                'provisional' => $uace['provisional'],
                'total_marks' => $totalMarks,
                'average' => round($average, 2),
                'principal_points' => $uace['principal_points'],
                'subsidiary_points' => $uace['subsidiary_points'],
                'total_points' => $uace['total_points'],
                'max_points' => $uace['max_points'],
                'subject_count' => count($uace['principals']) + count($uace['subsidiaries']),
                'position' => $position,
                'class_size' => $classSize,
                'remarks' => [
                    'conduct_required' => false,
                    'teacher_comment_required' => true,
                    'head_comment_required' => true,
                ],
            ];
        }

        $ranges = AssessmentGradingService::rangesForExam($exam, $schoolClass);
        $combination = $enrollment?->subjectCombination;
        $combination?->loadMissing('subjects');

        $breakdown = UgandaGrading::uaceBreakdown($grades, $combination, $ranges);

        // CHANGED (A-Level rebuild): was a flat all-subjects points list capped at 20 —
        // now principals and subsidiaries are separated per the student's combination.
        return [
            'format' => 'a-level',
            'format_label' => 'A-Level School Report (UACE)',
            // CHANGED (UACE paper rebuild): no paper results for this sitting — legacy
            // blended display, shown as-is and flagged historical (never recomputed).
            'paper_based' => false,
            'legacy' => true,
            'subjects' => collect($breakdown['principals']),
            'subsidiaries' => collect($breakdown['subsidiaries']),
            'excluded_subjects' => $breakdown['excluded'],
            'combination_code' => $combination?->code,
            'combination_name' => $combination?->name,
            'has_combination' => $breakdown['has_combination'],
            'total_marks' => $totalMarks,
            'average' => round($average, 2),
            'principal_points' => $breakdown['principal_points'],
            'subsidiary_points' => $breakdown['subsidiary_points'],
            'total_points' => $breakdown['total_points'],
            'max_points' => $breakdown['max_points'],
            'subject_count' => count($breakdown['principals']) + count($breakdown['subsidiaries']),
            'position' => $position,
            'class_size' => $classSize,
            'remarks' => [
                'conduct_required' => false,
                'teacher_comment_required' => true,
                'head_comment_required' => true,
            ],
        ];
    }

    /**
     * Get PRIMARY achievement level based on marks percentage.
     */
    private static function getPrimaryAchievementLevel(float $marks): string
    {
        if ($marks >= 90) return 'Excellent';
        if ($marks >= 80) return 'Very Good';
        if ($marks >= 70) return 'Good';
        if ($marks >= 60) return 'Satisfactory';
        if ($marks >= 50) return 'Fair';
        return 'Poor';
    }

    /**
     * Get color for achievement level badge.
     */
    private static function getAchievementLevelColor(string $level): string
    {
        return match ($level) {
            'Excellent', 'Very Good' => 'bg-green-100 text-green-800',
            'Good' => 'bg-blue-100 text-blue-800',
            'Satisfactory' => 'bg-yellow-100 text-yellow-800',
            'Fair' => 'bg-orange-100 text-orange-800',
            'Poor' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get color for O-level competency grade badge (A-E).
     */
    private static function getOLevelGradeColor(string $grade): string
    {
        return match ($grade) {
            'A', 'B' => 'bg-green-100 text-green-800',
            'C' => 'bg-blue-100 text-blue-800',
            'D' => 'bg-yellow-100 text-yellow-800',
            'E' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
