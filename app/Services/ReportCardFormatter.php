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
        ?SchoolClass $schoolClass = null
    ): array {
        // CHANGED: was `match ($exam->assessment_format)` — now resolves 'auto' via the class.
        return match (AssessmentGradingService::resolveFormat($exam->assessment_format, $schoolClass)) {
            'primary' => self::formatPrimary($grades, $totalMarks, $average, $position, $classSize, $exam, $schoolClass),
            'o-level' => self::formatOLevel($grades, $totalMarks, $average, $position, $classSize, $exam, $schoolClass),
            'a-level' => self::formatALevel($grades, $totalMarks, $average, $position, $classSize, $exam, $schoolClass),
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
     * Shows grade (A-E) + points (1-5, lower is better) + descriptors.
     * For composite reports: aggregates competency scores across component exams.
     */
    private static function formatOLevel(
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
            $subjectResult = AssessmentGradingService::resolve($marks, $ranges);

            // Include component breakdown for multi-term display
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

            return [
                'subject' => $grade->subject->name,
                'raw_marks' => $marks,
                'grade' => $subjectResult['grade'],
                'points' => $subjectResult['points'],
                'descriptor' => $subjectResult['description'],
                'color' => self::getOLevelGradeColor($subjectResult['grade']),
                'components' => $components,
            ];
        });

        $totalPoints = (int) $gradedSubjects->sum('points');
        $subjectCount = $gradedSubjects->count();
        $averagePoints = $subjectCount > 0 ? round($totalPoints / $subjectCount, 2) : 0;
        $overallGrade = AssessmentGradingService::resolve($average, $ranges)['grade'];
        $overallDescriptor = AssessmentGradingService::resolve($average, $ranges)['description'];

        return [
            'format' => 'o-level',
            'format_label' => 'O-Level School Report (Competency-Based)',
            'subjects' => $gradedSubjects,
            'total_marks' => $totalMarks,
            'average' => round($average, 2),
            'total_points' => $totalPoints,
            'average_points' => $averagePoints,
            'subject_count' => $subjectCount,
            'overall_grade' => $overallGrade,
            'overall_descriptor' => $overallDescriptor,
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
     * A-LEVEL FORMAT: Points-based (0-20 max per subject)
     * Each subject shows: Marks → Points (0-20 scale)
     * Total: Sum of all subject points (can be 60+ if 3+ subjects)
     */
    private static function formatALevel(
        Collection $grades,
        float $totalMarks,
        float $average,
        ?int $position,
        ?int $classSize,
        Exam $exam,
        ?SchoolClass $schoolClass = null // CHANGED: class-aware ranges for 'auto' format
    ): array {
        $maxPoints = (int) setting('alevel_points_max', 20);
        $ranges = AssessmentGradingService::rangesForExam($exam, $schoolClass);

        $gradedSubjects = $grades->map(function ($grade) use ($ranges) {
            $marks = $grade->marks_obtained ?? 0;
            $uace = AssessmentGradingService::resolve($marks, $ranges);

            return [
                'subject' => $grade->subject->name,
                'marks' => $marks,
                'grade' => $uace['grade'],
                'points' => (int) ($uace['points'] ?? 0),
            ];
        });

        $rawPoints = $gradedSubjects->sum('points');
        $totalPoints = min($rawPoints, $maxPoints);
        $subjectCount = $gradedSubjects->count();
        $averagePoints = $subjectCount > 0 ? round($rawPoints / $subjectCount, 2) : 0;

        return [
            'format' => 'a-level',
            'format_label' => 'A-Level School Report',
            'subjects' => $gradedSubjects,
            'total_marks' => $totalMarks,
            'total_points' => $totalPoints,
            'raw_points' => $rawPoints,
            'max_points' => $maxPoints,
            'average_points' => $averagePoints,
            'subject_count' => $subjectCount,
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
