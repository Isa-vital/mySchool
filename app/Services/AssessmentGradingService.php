<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\GradingScale;
use App\Models\SchoolClass;

class AssessmentGradingService
{
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

        $fallback = $ranges[array_key_last($ranges)] ?? ['grade' => '-', 'points' => null, 'description' => '-'];

        return [
            'grade' => (string) $fallback['grade'],
            'points' => isset($fallback['points']) && $fallback['points'] !== null ? (float) $fallback['points'] : null,
            'description' => $fallback['description'] ?? (string) $fallback['grade'],
        ];
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
        return [
            // CHANGED (A4): official UNEB/NCDC descriptors — was Excellent/Very Good/
            // Satisfactory/Basic/Developing Competency.
            ['grade' => 'A', 'min' => 80, 'max' => 100, 'points' => 1, 'description' => 'Exceptional'],
            ['grade' => 'B', 'min' => 65, 'max' => 79.99, 'points' => 2, 'description' => 'Outstanding'],
            ['grade' => 'C', 'min' => 50, 'max' => 64.99, 'points' => 3, 'description' => 'Satisfactory'],
            ['grade' => 'D', 'min' => 35, 'max' => 49.99, 'points' => 4, 'description' => 'Basic'],
            ['grade' => 'E', 'min' => 0, 'max' => 34.99, 'points' => 5, 'description' => 'Elementary'],
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
