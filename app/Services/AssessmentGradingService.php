<?php

namespace App\Services;

use App\Models\ActivityScore;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\GradingScale;
use App\Models\SchoolClass;
use App\Models\Subject;

class AssessmentGradingService
{
    // Grading outcome states: an unassessed subject must surface explicitly instead
    // of silently landing in the lowest band (see resolveMark / computeOLevelFinalMark).
    public const STATUS_GRADED = 'graded';
    public const STATUS_INCOMPLETE = 'incomplete';
    public const STATUS_NOT_YET_ASSESSED = 'not_yet_assessed';

    // CHANGED: accept optional class so 'auto' format resolves per student's class
    // (P.1-P.7 => primary, S.1-S.4 => o-level, S.5-S.6 => a-level).
    public static function rangesForExam(Exam $exam, ?SchoolClass $class = null): array
    {
        $gradingScale = $exam->gradingScale;

        // CHANGED: was `$exam->assessment_format ?? 'primary'` — now resolves 'auto' from class.
        return self::rangesForFormat(self::resolveFormat($exam->assessment_format, $class), $gradingScale);
    }

    /**
     * Resolve the effective assessment format.
     * When the exam/setting format is 'auto' (or missing), derive it from the
     * class category: primary classes => primary, S.1-S.4 => o-level, S.5-S.6 => a-level.
     */
    public static function resolveFormat(?string $format, ?SchoolClass $class = null): string
    {
        if (in_array($format, ['primary', 'o-level', 'a-level'], true)) {
            return $format;
        }

        // 'auto' or unset: derive from the student's class when known.
        if ($class) {
            return self::formatForClass($class);
        }

        $settingFormat = setting('report_card_format', 'auto');

        return in_array($settingFormat, ['primary', 'o-level', 'a-level'], true) ? $settingFormat : 'primary';
    }

    public static function formatForClass(SchoolClass $class): string
    {
        return match ($class->category()) {
            'o_level' => 'o-level',
            'a_level' => 'a-level',
            default => 'primary',
        };
    }

    public static function rangesForFormat(string $format, ?GradingScale $gradingScale = null): array
    {
        if ($gradingScale && $gradingScale->exists) {
            $ranges = $gradingScale->ranges()->get()->map(function ($range) {
                return [
                    'grade' => $range->grade,
                    'min' => (float) $range->min_mark,
                    'max' => (float) $range->max_mark,
                    'points' => $range->grade_point !== null ? (float) $range->grade_point : null,
                    'description' => $range->description,
                ];
            })->all();

            return self::sortRanges($ranges);
        }

        return self::sortRanges(match ($format) {
            'o-level' => self::configuredRanges('olevel_competency_scale', self::defaultOLevelRanges()),
            'a-level' => self::configuredRanges('alevel_grade_scale', self::defaultALevelRanges()),
            default => self::configuredRanges('primary_achievement_levels', self::defaultPrimaryRanges()),
        });
    }

    // CHANGED: optional class so grades resolve against the class-appropriate scale when format is 'auto'.
    public static function resolveForExam(Exam $exam, float $marks, ?SchoolClass $class = null): array
    {
        return self::resolve($marks, self::rangesForExam($exam, $class));
    }

    public static function resolve(float $marks, array $ranges): array
    {
        foreach ($ranges as $range) {
            if ($marks >= (float) $range['min'] && $marks <= (float) $range['max']) {
                return [
                    'grade' => (string) $range['grade'],
                    'points' => isset($range['points']) && $range['points'] !== null ? (float) $range['points'] : null,
                    'description' => $range['description'] ?? (string) $range['grade'],
                ];
            }
        }

        // Defensive fallback for a genuinely out-of-range NUMERIC input only (e.g. a
        // negative entry glitch). "No mark entered" must never reach this method —
        // use resolveMark(), which short-circuits null to NOT YET ASSESSED first.
        $fallback = $ranges[array_key_last($ranges)] ?? ['grade' => '-', 'points' => null, 'description' => '-'];

        return [
            'grade' => (string) $fallback['grade'],
            'points' => isset($fallback['points']) && $fallback['points'] !== null ? (float) $fallback['points'] : null,
            'description' => $fallback['description'] ?? (string) $fallback['grade'],
        ];
    }

    /**
     * Null-safe banding: a missing mark short-circuits to NOT YET ASSESSED before
     * any range matching runs, so it can never fall through to the lowest band.
     */
    public static function resolveMark(?float $marks, array $ranges): array
    {
        if ($marks === null) {
            return [
                'status' => self::STATUS_NOT_YET_ASSESSED,
                'grade' => null,
                'points' => null,
                'description' => 'NOT YET ASSESSED',
            ];
        }

        return ['status' => self::STATUS_GRADED] + self::resolve($marks, $ranges);
    }

    /** Highest points value in a scale (denominator for Total Points summaries). */
    public static function maxPointsForRanges(array $ranges): float
    {
        return (float) max(array_map(fn($range) => (float) ($range['points'] ?? 0), $ranges) ?: [0]);
    }

    /** Max raw score per Activity of Integration ('activity_scale_config' setting, default 3.0). */
    public static function activityMaxScore(): float
    {
        $config = json_decode((string) setting('activity_scale_config', ''), true);
        $max = is_array($config) ? (float) ($config['activity_max_score'] ?? 0) : 0.0;

        return $max > 0 ? $max : 3.0;
    }

    /** Max raw score for project work ('activity_scale_config' setting, default 10 — matches real cards). */
    public static function projectMaxScore(): float
    {
        $config = json_decode((string) setting('activity_scale_config', ''), true);
        $max = is_array($config) ? (float) ($config['project_max_score'] ?? 0) : 0.0;

        return $max > 0 ? $max : 10.0;
    }

    /**
     * CA/EOT weight split ('ca_weight_config' setting, default 20/80).
     * Invalid configs (non-positive or not summing to 100) fall back to 20/80.
     *
     * @return array{ca: float, eot: float}
     */
    public static function caWeights(): array
    {
        $config = json_decode((string) setting('ca_weight_config', ''), true);
        $ca = is_array($config) ? (float) ($config['ca'] ?? 0) : 0.0;
        $eot = is_array($config) ? (float) ($config['eot'] ?? 0) : 0.0;

        if ($ca <= 0 || $eot <= 0 || abs($ca + $eot - 100) > 0.001) {
            return ['ca' => 20.0, 'eot' => 80.0];
        }

        return ['ca' => $ca, 'eot' => $eot];
    }

    /**
     * O-Level final mark from the activity/CA layer:
     *   ca_mark = (mean of scored activities / activity_max) * ca_weight
     *   final_mark = ca_mark + EOT score (scaled to the EOT weight when max differs)
     *
     * Single entry point for O-Level grading — legacy rows entered before the
     * CA/EOT split (no eot_status, no activities) are handled HERE by treating the
     * stored marks_obtained as the final mark, so no parallel grading path exists.
     *
     * Returns status graded | incomplete (activities exist, EOT missing) |
     * not_yet_assessed (nothing assessed), plus all intermediate values for audit.
     */
    public static function computeOLevelFinalMark(Enrollment $enrollment, Subject $subject, Exam $exam, ?Grade $grade = null, ?array $ranges = null): array
    {
        $ranges ??= self::rangesForExam($exam, $enrollment->schoolClass);

        $grade ??= Grade::where('exam_id', $exam->id)
            ->where('student_id', $enrollment->student_id)
            ->where('subject_id', $subject->id)
            ->whereNull('subject_component_id')
            ->first();

        $weights = self::caWeights();
        $activityMax = self::activityMaxScore();

        $scores = ActivityScore::where('enrollment_id', $enrollment->id)
            ->where('subject_id', $subject->id)
            ->where('exam_id', $exam->id)
            ->where('status', 'scored')
            ->orderBy('activity_number')
            ->get();

        // Unfilled activity slots are excluded from the average — never treated as 0.
        $activityAvg = $scores->isEmpty() ? null : round((float) $scores->avg('raw_score'), 2);
        $caMark = $activityAvg === null ? 0.0 : round(($activityAvg / $activityMax) * $weights['ca'], 2);

        $eotRaw = $grade?->eot_raw_score !== null ? (float) $grade->eot_raw_score : null;
        $eotMax = (float) ($grade?->eot_max_score ?? $weights['eot']);

        // Project work: own score and own grade — NEVER merged into the final mark.
        $projectRaw = $grade?->project_score_raw !== null ? (float) $grade->project_score_raw : null;
        $projectMax = (float) ($grade?->project_score_max ?? self::projectMaxScore());
        $projectGrade = null;
        if ($projectRaw !== null && $projectMax > 0) {
            $projectGrade = self::resolve(($projectRaw / $projectMax) * 100, $ranges)['grade'];
        }

        $base = [
            'activity_scores' => $scores->mapWithKeys(fn($s) => [(int) $s->activity_number => (float) $s->raw_score])->all(),
            'activity_avg' => $activityAvg,
            'activity_max' => $activityMax,
            'ca_mark' => $activityAvg === null ? null : $caMark,
            'ca_total' => $weights['ca'],
            'eot_raw_score' => $eotRaw,
            'eot_max_score' => $eotMax,
            'eot_total' => $weights['eot'],
            'eot_status' => $grade?->eot_status,
            'identifier' => $grade?->identifier !== null ? (int) $grade->identifier : null,
            'project_score_raw' => $projectRaw,
            'project_score_max' => $projectMax,
            'project_status' => $grade?->project_status,
            'project_grade' => $projectGrade,
            'final_mark' => null,
        ];

        // Legacy single-mark row: marks_obtained IS the final mark (null still
        // short-circuits to NOT YET ASSESSED via resolveMark).
        if ($scores->isEmpty() && $grade && $grade->eot_status === null) {
            $final = $grade->marks_obtained !== null ? (float) $grade->marks_obtained : null;

            return array_merge($base, self::resolveMark($final, $ranges), ['final_mark' => $final]);
        }

        if (($grade?->eot_status) !== 'scored' || $eotRaw === null) {
            // No usable EOT: nothing assessed at all => NOT YET ASSESSED; activities
            // logged but EOT outstanding/absent/withheld => INCOMPLETE. No final mark either way.
            $status = $activityAvg === null ? self::STATUS_NOT_YET_ASSESSED : self::STATUS_INCOMPLETE;

            return array_merge($base, [
                'status' => $status,
                'grade' => null,
                'points' => null,
                'description' => $status === self::STATUS_INCOMPLETE ? 'INCOMPLETE' : 'NOT YET ASSESSED',
            ]);
        }

        // EOT scaled to its weight when scored out of a different max (default 80/80 = as-is).
        $eotComponent = $eotMax > 0 ? round(($eotRaw / $eotMax) * $weights['eot'], 2) : 0.0;
        $final = round($caMark + $eotComponent, 2);

        return array_merge($base, self::resolveMark($final, $ranges), ['final_mark' => $final]);
    }

    // CHANGED: optional class so grade-entry previews match the class-appropriate scale when format is 'auto'.
    public static function previewRangesForExam(Exam $exam, ?SchoolClass $class = null): array
    {
        return array_map(fn($range) => [
            'grade' => $range['grade'],
            'min' => (float) $range['min'],
            'max' => (float) $range['max'],
        ], self::rangesForExam($exam, $class));
    }

    // CHANGED (A5): public accessor so other services (UgandaGrading) resolve their
    // boundaries from the SAME configurable source instead of hardcoding values.
    public static function rangesForSetting(string $settingKey, array $fallback): array
    {
        return self::sortRanges(self::configuredRanges($settingKey, $fallback));
    }

    private static function configuredRanges(string $settingKey, array $fallback): array
    {
        $configured = setting($settingKey, null);
        if (! is_string($configured) || trim($configured) === '') {
            return $fallback;
        }

        $decoded = json_decode($configured, true);
        if (! is_array($decoded)) {
            return $fallback;
        }

        $ranges = [];
        foreach ($decoded as $item) {
            if (! is_array($item) || ! isset($item['grade'], $item['min'], $item['max'])) {
                continue;
            }

            $ranges[] = [
                'grade' => (string) $item['grade'],
                'min' => (float) $item['min'],
                'max' => (float) $item['max'],
                'points' => isset($item['points']) && $item['points'] !== '' ? (float) $item['points'] : null,
                'description' => $item['description'] ?? (string) $item['grade'],
            ];
        }

        return $ranges !== [] ? $ranges : $fallback;
    }

    private static function sortRanges(array $ranges): array
    {
        usort($ranges, fn($left, $right) => (float) $right['min'] <=> (float) $left['min']);

        return $ranges;
    }

    private static function defaultPrimaryRanges(): array
    {
        return [
            ['grade' => 'Excellent', 'min' => 90, 'max' => 100, 'points' => 1, 'description' => 'Excellent'],
            ['grade' => 'Very Good', 'min' => 80, 'max' => 89.99, 'points' => 2, 'description' => 'Very Good'],
            ['grade' => 'Good', 'min' => 70, 'max' => 79.99, 'points' => 3, 'description' => 'Good'],
            ['grade' => 'Satisfactory', 'min' => 60, 'max' => 69.99, 'points' => 4, 'description' => 'Satisfactory'],
            ['grade' => 'Fair', 'min' => 50, 'max' => 59.99, 'points' => 5, 'description' => 'Fair'],
            ['grade' => 'Poor', 'min' => 0, 'max' => 49.99, 'points' => 6, 'description' => 'Poor'],
        ];
    }

    private static function defaultOLevelRanges(): array
    {
        // Boundaries and points corrected to the confirmed UCE model (verified against
        // a real report card): higher points = better (A=4 … E=0). The previous scale
        // was inverted (A=1 … E=5) and its B/C/D/E boundaries were shifted:
        // ['grade' => 'A', 'min' => 80, 'max' => 100, 'points' => 1, 'description' => 'Exceptional'],
        // ['grade' => 'B', 'min' => 65, 'max' => 79.99, 'points' => 2, 'description' => 'Outstanding'],
        // ['grade' => 'C', 'min' => 50, 'max' => 64.99, 'points' => 3, 'description' => 'Satisfactory'],
        // ['grade' => 'D', 'min' => 35, 'max' => 49.99, 'points' => 4, 'description' => 'Basic'],
        // ['grade' => 'E', 'min' => 0, 'max' => 34.99, 'points' => 5, 'description' => 'Elementary'],
        return [
            ['grade' => 'A', 'min' => 80, 'max' => 100, 'points' => 4, 'description' => 'Exceptional'],
            ['grade' => 'B', 'min' => 60, 'max' => 79.99, 'points' => 3, 'description' => 'Outstanding'],
            ['grade' => 'C', 'min' => 50, 'max' => 59.99, 'points' => 2, 'description' => 'Satisfactory'],
            ['grade' => 'D', 'min' => 40, 'max' => 49.99, 'points' => 1, 'description' => 'Basic'],
            ['grade' => 'E', 'min' => 0, 'max' => 39.99, 'points' => 0, 'description' => 'Elementary'],
        ];
    }

    private static function defaultALevelRanges(): array
    {
        return [
            ['grade' => 'A', 'min' => 80, 'max' => 100, 'points' => 6, 'description' => 'Excellent'],
            ['grade' => 'B', 'min' => 70, 'max' => 79.99, 'points' => 5, 'description' => 'Very Good'],
            ['grade' => 'C', 'min' => 60, 'max' => 69.99, 'points' => 4, 'description' => 'Good'],
            ['grade' => 'D', 'min' => 55, 'max' => 59.99, 'points' => 3, 'description' => 'Fairly Good'],
            ['grade' => 'E', 'min' => 50, 'max' => 54.99, 'points' => 2, 'description' => 'Pass'],
            ['grade' => 'O', 'min' => 40, 'max' => 49.99, 'points' => 1, 'description' => 'Subsidiary Pass'],
            ['grade' => 'F', 'min' => 0, 'max' => 39.99, 'points' => 0, 'description' => 'Fail'],
        ];
    }
}
