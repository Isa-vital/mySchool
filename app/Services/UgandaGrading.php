<?php

namespace App\Services;

use App\Models\SubjectCombination;
use Illuminate\Support\Collection;

/**
 * Encapsulates Uganda national-examination grading rules (UNEB).
 *
 *  - PLE  : 4 subjects, each D1(1)..F9(9); aggregate = sum of 4 best => Division I-IV / U.
 *  - UCE  : best 8 subjects, each scored 1..9; aggregate => Division 1-4 / U
 *           (legacy aggregate model). The new lower-secondary curriculum also
 *           reports competency achievement levels A-E (see achievementLevel()).
 *  - UACE : principal subjects A(6)..F(0); total principal points.
 */
class UgandaGrading
{
    /**
     * O-level (new lower secondary curriculum) competency level from score.
     * Levels: A, B, C, D, E.
     */
    public static function oLevelCompetencyLevel(float $marks): string
    {
        // CHANGED (A5): boundaries now come from the same configurable source
        // ('olevel_competency_scale' setting) used for report rendering — previously
        // hardcoded here (80/65/50/35), which could silently disagree with config.
        $ranges = AssessmentGradingService::rangesForFormat('o-level');

        return (string) AssessmentGradingService::resolve($marks, $ranges)['grade'];
    }

    /**
     * O-level curriculum points mapped from competency levels.
     * Higher points indicate better performance (A=4 … E=0).
     */
    public static function oLevelCompetencyPoints(string $level): int
    {
        // Corrected from the inverted A=1..E=5 map — an E student scored MORE
        // points than an A student. Confirmed scale (real report card): A=4..E=0.
        // $defaultMap = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5];
        $defaultMap = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];

        // CHANGED: allow schools to align to official circular updates without code changes.
        $configured = setting('olevel_competency_points', null);
        $map = $defaultMap;
        if (is_string($configured)) {
            $decoded = json_decode($configured, true);
            if (is_array($decoded)) {
                foreach ($defaultMap as $grade => $point) {
                    if (isset($decoded[$grade]) && is_numeric($decoded[$grade])) {
                        $map[$grade] = (int) $decoded[$grade];
                    }
                }
            }
        }

        $grade = strtoupper(trim($level));
        return $map[$grade] ?? $map['E'];
    }

    /**
     * O-level subject result in points-based competency format.
     * Returns level, points and a brief descriptor.
     */
    public static function oLevelSubjectResult(float $marks): array
    {
        $level = self::oLevelCompetencyLevel($marks);
        $points = self::oLevelCompetencyPoints($level);

        $descriptor = match ($level) {
            // CHANGED (A4): official UNEB/NCDC wording — was Excellent/Very Good/
            // Satisfactory/Basic/Developing Competency.
            'A' => 'Exceptional',
            'B' => 'Outstanding',
            'C' => 'Satisfactory',
            'D' => 'Basic',
            default => 'Elementary',
        };

        return ['level' => $level, 'points' => $points, 'description' => $descriptor];
    }

    /**
     * O-level overall competency level from the average PERCENTAGE mark.
     * Delegates to the same configurable bands each subject is graded with, so the
     * overall grade can never disagree with the report summary.
     */
    public static function oLevelOverallLevel(float $averagePercentage): string
    {
        // The old points-based thresholds could disagree with the report summary's
        // percentage-based overall grade for the same student:
        // return match (true) {
        //     $avgPoints <= 1.5 => 'A',
        //     $avgPoints <= 2.5 => 'B',
        //     $avgPoints <= 3.5 => 'C',
        //     $avgPoints <= 4.5 => 'D',
        //     default => 'E',
        // };
        return self::oLevelCompetencyLevel($averagePercentage);
    }

    /**
     * Map a percentage mark to a UNEB subject grade (D1..F9) and its numeric value.
     */
    public static function subjectGrade(float $marks): array
    {
        // CHANGED (A5): stanine boundaries now configurable via the 'ple_stanine_scale'
        // setting (was hardcoded). Also fixes fractional-mark gaps: 89.5 previously fell
        // between D2(max 89) and D1(min 90) and wrongly resolved to F9.
        $ranges = AssessmentGradingService::rangesForSetting('ple_stanine_scale', self::defaultStanineRanges());
        $resolved = AssessmentGradingService::resolve($marks, $ranges);

        return [
            'grade' => (string) $resolved['grade'],
            'value' => (int) ($resolved['points'] ?? 9),
            'description' => (string) ($resolved['description'] ?? $resolved['grade']),
        ];
    }

    /**
     * CHANGED (A5): default UNEB stanine bands — used when no 'ple_stanine_scale'
     * setting exists. Values mirror the previous hardcoded map.
     */
    private static function defaultStanineRanges(): array
    {
        return [
            ['grade' => 'D1', 'min' => 90, 'max' => 100, 'points' => 1, 'description' => 'Distinction'],
            ['grade' => 'D2', 'min' => 80, 'max' => 89.99, 'points' => 2, 'description' => 'Distinction'],
            ['grade' => 'C3', 'min' => 70, 'max' => 79.99, 'points' => 3, 'description' => 'Credit'],
            ['grade' => 'C4', 'min' => 60, 'max' => 69.99, 'points' => 4, 'description' => 'Credit'],
            ['grade' => 'C5', 'min' => 55, 'max' => 59.99, 'points' => 5, 'description' => 'Credit'],
            ['grade' => 'C6', 'min' => 50, 'max' => 54.99, 'points' => 6, 'description' => 'Credit'],
            ['grade' => 'P7', 'min' => 45, 'max' => 49.99, 'points' => 7, 'description' => 'Pass'],
            ['grade' => 'P8', 'min' => 40, 'max' => 44.99, 'points' => 8, 'description' => 'Pass'],
            ['grade' => 'F9', 'min' => 0, 'max' => 39.99, 'points' => 9, 'description' => 'Failure'],
        ];
    }

    /**
     * PLE aggregate from the four core subjects' grade values (1-9 each).
     * Best four are used (range 4-36).
     */
    public static function pleAggregate(array $gradeValues): int
    {
        sort($gradeValues);
        return (int) array_sum(array_slice($gradeValues, 0, 4));
    }

    /**
     * PLE division from aggregate (UNEB scale).
     */
    public static function pleDivision(int $aggregate): string
    {
        return match (true) {
            $aggregate >= 4 && $aggregate <= 12 => 'Division 1',
            $aggregate >= 13 && $aggregate <= 23 => 'Division 2',
            $aggregate >= 24 && $aggregate <= 29 => 'Division 3',
            $aggregate >= 30 && $aggregate <= 34 => 'Division 4',
            default => 'Division U', // ungraded
        };
    }

    /**
     * UCE aggregate from best 8 subjects' grade values (1-9 each), range 8-72.
     * HISTORICAL ONLY: retired with the CBC transition — must not feed current
     * S.4 report labels (see nationalResult()). Kept for pre-CBC records.
     */
    public static function uceAggregate(array $gradeValues): int
    {
        sort($gradeValues);
        return (int) array_sum(array_slice($gradeValues, 0, 8));
    }

    /**
     * UCE division from aggregate (legacy O-level grouping).
     * HISTORICAL ONLY: divisions were retired with the CBC transition.
     */
    public static function uceDivision(int $aggregate): string
    {
        return match (true) {
            $aggregate >= 8 && $aggregate <= 32 => 'Division 1',
            $aggregate >= 33 && $aggregate <= 45 => 'Division 2',
            $aggregate >= 46 && $aggregate <= 58 => 'Division 3',
            $aggregate >= 59 && $aggregate <= 68 => 'Division 4',
            default => 'Division U',
        };
    }

    /**
     * New lower-secondary curriculum competency achievement level (A-E)
     * derived from a percentage score (continuous assessment + exam).
     */
    public static function achievementLevel(float $marks): string
    {
        // CHANGED (A5): delegates to the config-driven resolver — was a duplicated
        // hardcoded copy of oLevelCompetencyLevel().
        return self::oLevelCompetencyLevel($marks);
    }

    /**
     * UACE principal-subject grade and points from a percentage mark.
     * A=6, B=5, C=4, D=3, E=2, O=1 (subsidiary pass), F=0.
     */
    public static function uaceGrade(float $marks): array
    {
        // CHANGED (A5): boundaries now come from the 'alevel_grade_scale' setting
        // (same source the report renderer uses) — previously hardcoded here.
        // The seeded setting mirrors the old map exactly.
        $ranges = AssessmentGradingService::rangesForFormat('a-level');
        $resolved = AssessmentGradingService::resolve($marks, $ranges);

        return [
            'grade' => (string) $resolved['grade'],
            'points' => (int) ($resolved['points'] ?? 0),
        ];
    }

    /**
     * Total UACE principal points from an array of percentage marks.
     */
    public static function uacePoints(array $marks): int
    {
        $total = 0;
        foreach ($marks as $m) {
            $total += self::uaceGrade((float) $m)['points'];
        }
        return $total;
    }

    /**
     * CHANGED (A-Level rebuild): split a student's composed grade rows into UACE
     * principal (A-F, 6-0 pts) and subsidiary (pass = 1 pt) sections using their
     * combination. No combination => everything graded as principal (legacy) and
     * flagged so reports can warn. Subjects outside the combination are excluded.
     */
    public static function uaceBreakdown(Collection $grades, ?SubjectCombination $combination, array $ranges): array
    {
        $passMin = (float) setting('alevel_subsidiary_pass_min', 40);

        $principalIds = [];
        $subsidiaryIds = [];
        if ($combination) {
            foreach ($combination->subjects as $subject) {
                if ($subject->pivot->is_principal) {
                    $principalIds[] = (int) $subject->id;
                } else {
                    $subsidiaryIds[] = (int) $subject->id;
                }
            }
        }

        $principals = [];
        $subsidiaries = [];
        $excluded = [];
        foreach ($grades as $grade) {
            $marks = (float) ($grade->marks_obtained ?? 0);
            // Composite rows carry the subject model; direct Grade rows carry subject_id.
            $subjectId = (int) ($grade->subject_id ?? $grade->subject?->id ?? 0);
            $subjectName = $grade->subject->name ?? '';

            if (! $combination || in_array($subjectId, $principalIds, true)) {
                $resolved = AssessmentGradingService::resolve($marks, $ranges);
                $principals[] = [
                    'subject' => $subjectName,
                    'marks' => round($marks, 1),
                    'grade' => (string) $resolved['grade'],
                    'points' => (int) ($resolved['points'] ?? 0),
                ];
            } elseif (in_array($subjectId, $subsidiaryIds, true)) {
                $pass = $marks >= $passMin;
                $subsidiaries[] = [
                    'subject' => $subjectName,
                    'marks' => round($marks, 1),
                    'result' => $pass ? 'Pass' : 'Fail',
                    'points' => $pass ? 1 : 0,
                ];
            } else {
                $excluded[] = $subjectName;
            }
        }

        $maxPerPrincipal = (int) max(array_map(fn($r) => (float) ($r['points'] ?? 0), $ranges) ?: [6]);
        $principalSlots = $combination ? count($principalIds) : count($principals);
        $subsidiarySlots = $combination ? count($subsidiaryIds) : count($subsidiaries);

        $principalPoints = (int) array_sum(array_column($principals, 'points'));
        $subsidiaryPoints = (int) array_sum(array_column($subsidiaries, 'points'));

        return [
            'principals' => $principals,
            'subsidiaries' => $subsidiaries,
            'excluded' => $excluded,
            'principal_points' => $principalPoints,
            'subsidiary_points' => $subsidiaryPoints,
            'total_points' => $principalPoints + $subsidiaryPoints,
            'max_points' => ($principalSlots * $maxPerPrincipal) + $subsidiarySlots,
            'has_combination' => (bool) $combination,
        ];
    }

    /**
     * Produce the national result string for a class category + grades collection.
     * $marks is an array of percentage scores.
     *
     * @return array{label:?string, aggregate:?int}
     */
    public static function nationalResult(?string $nationalExam, array $marks): array
    {
        if (empty($marks) || ! $nationalExam) {
            return ['label' => null, 'aggregate' => null];
        }

        return match ($nationalExam) {
            'PLE' => (function () use ($marks) {
                // Stanine conversion is now PLE-only (UCE no longer uses it).
                $values = array_map(fn($m) => self::subjectGrade((float) $m)['value'], $marks);
                $agg = self::pleAggregate($values);
                return ['label' => self::pleDivision($agg), 'aggregate' => $agg];
            })(),
            'UCE' => (function () use ($marks) {
                // Divisions and D1-F9 stanines were retired for UCE with the CBC
                // transition — S.4 now reports the competency-based overall level
                // from the same path as regular subject grading. Legacy path:
                // $agg = self::uceAggregate($values);
                // return ['label' => self::uceDivision($agg), 'aggregate' => $agg];
                $avg = array_sum($marks) / count($marks);
                $result = self::oLevelSubjectResult($avg);
                return [
                    'label' => 'Achievement Level ' . $result['level'] . ' — ' . $result['description'],
                    'aggregate' => null,
                ];
            })(),
            'UACE' => (function () use ($marks) {
                $points = self::uacePoints($marks);
                return ['label' => $points . ' points', 'aggregate' => $points];
            })(),
            default => ['label' => null, 'aggregate' => null],
        };
    }
}
