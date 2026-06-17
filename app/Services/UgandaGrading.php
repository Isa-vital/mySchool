<?php

namespace App\Services;

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
        // CHANGED: new curriculum competency levels.
        return match (true) {
            $marks >= 80 => 'A',
            $marks >= 65 => 'B',
            $marks >= 50 => 'C',
            $marks >= 35 => 'D',
            default => 'E',
        };
    }

    /**
     * O-level curriculum points mapped from competency levels.
     * Lower points indicate better performance.
     */
    public static function oLevelCompetencyPoints(string $level): int
    {
        $defaultMap = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5];

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
            'A' => 'Excellent Competency',
            'B' => 'Very Good Competency',
            'C' => 'Satisfactory Competency',
            'D' => 'Basic Competency',
            default => 'Developing Competency',
        };

        return ['level' => $level, 'points' => $points, 'description' => $descriptor];
    }

    /**
     * O-level overall competency level from average points.
     */
    public static function oLevelOverallLevel(float $avgPoints): string
    {
        return match (true) {
            $avgPoints <= 1.5 => 'A',
            $avgPoints <= 2.5 => 'B',
            $avgPoints <= 3.5 => 'C',
            $avgPoints <= 4.5 => 'D',
            default => 'E',
        };
    }

    /**
     * Map a percentage mark to a UNEB subject grade (D1..F9) and its numeric value.
     */
    public static function subjectGrade(float $marks): array
    {
        $map = [
            ['D1', 90, 100, 1, 'Distinction'],
            ['D2', 80, 89,  2, 'Distinction'],
            ['C3', 70, 79,  3, 'Credit'],
            ['C4', 60, 69,  4, 'Credit'],
            ['C5', 55, 59,  5, 'Credit'],
            ['C6', 50, 54,  6, 'Credit'],
            ['P7', 45, 49,  7, 'Pass'],
            ['P8', 40, 44,  8, 'Pass'],
            ['F9', 0,  39,  9, 'Failure'],
        ];

        foreach ($map as [$grade, $min, $max, $value, $desc]) {
            if ($marks >= $min && $marks <= $max) {
                return ['grade' => $grade, 'value' => $value, 'description' => $desc];
            }
        }

        return ['grade' => 'F9', 'value' => 9, 'description' => 'Failure'];
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
     */
    public static function uceAggregate(array $gradeValues): int
    {
        // CHANGED: legacy UCE aggregate retained for backward compatibility.
        sort($gradeValues);
        return (int) array_sum(array_slice($gradeValues, 0, 8));
    }

    /**
     * UCE division from aggregate (legacy O-level grouping).
     */
    public static function uceDivision(int $aggregate): string
    {
        // CHANGED: legacy UCE divisions retained for backward compatibility.
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
        return match (true) {
            $marks >= 80 => 'A',
            $marks >= 65 => 'B',
            $marks >= 50 => 'C',
            $marks >= 35 => 'D',
            default => 'E',
        };
    }

    /**
     * UACE principal-subject grade and points from a percentage mark.
     * A=6, B=5, C=4, D=3, E=2, O=1 (subsidiary pass), F=0.
     */
    public static function uaceGrade(float $marks): array
    {
        $map = [
            ['A', 80, 100, 6],
            ['B', 70, 79,  5],
            ['C', 60, 69,  4],
            ['D', 55, 59,  3],
            ['E', 50, 54,  2],
            ['O', 40, 49,  1],
            ['F', 0,  39,  0],
        ];

        foreach ($map as [$grade, $min, $max, $points]) {
            if ($marks >= $min && $marks <= $max) {
                return ['grade' => $grade, 'points' => $points];
            }
        }

        return ['grade' => 'F', 'points' => 0];
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

        $values = array_map(fn($m) => self::subjectGrade((float) $m)['value'], $marks);

        return match ($nationalExam) {
            'PLE' => (function () use ($values) {
                $agg = self::pleAggregate($values);
                return ['label' => self::pleDivision($agg), 'aggregate' => $agg];
            })(),
            'UCE' => (function () use ($values) {
                $agg = self::uceAggregate($values);
                return ['label' => self::uceDivision($agg), 'aggregate' => $agg];
            })(),
            'UACE' => (function () use ($marks) {
                $points = self::uacePoints($marks);
                return ['label' => $points . ' points', 'aggregate' => $points];
            })(),
            default => ['label' => null, 'aggregate' => null],
        };
    }
}
