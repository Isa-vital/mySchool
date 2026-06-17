<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Grade;
use Illuminate\Support\Collection;

class ReportCardFormatter
{
    /**
     * Format report data based on assessment format.
     */
    public static function format(
        Exam $exam,
        Collection $grades,
        float $totalMarks,
        float $average,
        ?int $position,
        ?int $classSize
    ): array {
        return match ($exam->assessment_format) {
            'primary' => self::formatPrimary($grades, $totalMarks, $average, $position, $classSize, $exam),
            'o-level' => self::formatOLevel($grades, $totalMarks, $average, $position, $classSize, $exam),
            'a-level' => self::formatALevel($grades, $totalMarks, $average, $position, $classSize, $exam),
            default => self::formatPrimary($grades, $totalMarks, $average, $position, $classSize, $exam),
        };
    }

    /**
     * PRIMARY FORMAT: Traditional marks + achievement levels
     * Each subject shows: Marks (0-100) → Achievement Level (Excellent/Good/Satisfactory/Fair/Poor)
     */
    private static function formatPrimary(
        Collection $grades,
        float $totalMarks,
        float $average,
        ?int $position,
        ?int $classSize,
        Exam $exam
    ): array {
        $gradedSubjects = $grades->map(function ($grade) {
            $marks = $grade->marks_obtained ?? 0;
            $level = self::getPrimaryAchievementLevel($marks);

            return [
                'subject' => $grade->subject->name,
                'marks' => $marks,
                'grade' => $level,
                'color' => self::getAchievementLevelColor($level),
            ];
        });

        $overallLevel = self::getPrimaryAchievementLevel($average);

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
     * O-LEVEL FORMAT: Marks → Grade (A-E) + Subject Ranking
     * Each subject shows: Marks (0-100) → Grade Letter (A/B/C/D/E)
     * Total: Aggregate score calculation
     */
    private static function formatOLevel(
        Collection $grades,
        float $totalMarks,
        float $average,
        ?int $position,
        ?int $classSize,
        Exam $exam
    ): array {
        // CHANGED: switched from A-E custom scale to Uganda-style O-level subject grades
        // (D1-F9) and best-8 aggregate calculation.
        $gradedSubjects = $grades->map(function ($grade) {
            $marks = $grade->marks_obtained ?? 0;
            $subjectGrade = UgandaGrading::subjectGrade((float) $marks);

            return [
                'subject' => $grade->subject->name,
                'marks' => $marks,
                'grade' => $subjectGrade['grade'],
                'points' => $subjectGrade['value'],
                'descriptor' => $subjectGrade['description'],
                'color' => self::getOLevelGradeColor($subjectGrade['grade']),
            ];
        });

        // CHANGED: O-level aggregate is based on best 8 subjects, lower is better.
        $aggregatePoints = UgandaGrading::uceAggregate($gradedSubjects->pluck('points')->all());
        $overallGrade = UgandaGrading::uceDivision($aggregatePoints);

        return [
            'format' => 'o-level',
            'format_label' => 'O-Level School Report',
            'subjects' => $gradedSubjects,
            'total_marks' => $totalMarks,
            'average' => round($average, 2),
            'aggregate_points' => $aggregatePoints,
            'overall_grade' => $overallGrade,
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
        Exam $exam
    ): array {
        $maxPoints = (int) setting('alevel_points_max', 20);

        // CHANGED: switched from linear 0-20 per-subject scaling to A-level grade points
        // and capped total out of configured maximum (default 20) for school reporting.
        $gradedSubjects = $grades->map(function ($grade) {
            $marks = $grade->marks_obtained ?? 0;
            $uace = UgandaGrading::uaceGrade((float) $marks);

            return [
                'subject' => $grade->subject->name,
                'marks' => $marks,
                'grade' => $uace['grade'],
                'points' => $uace['points'],
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
     * Get color for O-level grade badge (D1-F9).
     */
    private static function getOLevelGradeColor(string $grade): string
    {
        return match ($grade) {
            'D1', 'D2' => 'bg-green-100 text-green-800',
            'C3', 'C4', 'C5', 'C6' => 'bg-blue-100 text-blue-800',
            'P7', 'P8' => 'bg-yellow-100 text-yellow-800',
            'F9' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
