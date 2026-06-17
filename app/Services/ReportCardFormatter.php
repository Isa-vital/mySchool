<?php

namespace App\Services;

use App\Models\Exam;
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
        $ranges = AssessmentGradingService::rangesForExam($exam);

        $gradedSubjects = $grades->map(function ($grade) use ($ranges) {
            $marks = $grade->marks_obtained ?? 0;
            $resolved = AssessmentGradingService::resolve($marks, $ranges);

            return [
                'subject' => $grade->subject->name,
                'marks' => $marks,
                'grade' => $resolved['grade'],
                'color' => self::getAchievementLevelColor($resolved['grade']),
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
     * O-LEVEL FORMAT (new curriculum): Competency level + points.
     * Each subject shows: Level (A-E) + Points (1-5, lower is better)
     */
    private static function formatOLevel(
        Collection $grades,
        float $totalMarks,
        float $average,
        ?int $position,
        ?int $classSize,
        Exam $exam
    ): array {
        $ranges = AssessmentGradingService::rangesForExam($exam);

        $gradedSubjects = $grades->map(function ($grade) use ($ranges) {
            $marks = $grade->marks_obtained ?? 0;
            $subjectResult = AssessmentGradingService::resolve($marks, $ranges);

            return [
                'subject' => $grade->subject->name,
                'raw_marks' => $marks,
                'grade' => $subjectResult['grade'],
                'points' => $subjectResult['points'],
                'descriptor' => $subjectResult['description'],
                'color' => self::getOLevelGradeColor($subjectResult['grade']),
            ];
        });

        $totalPoints = (int) $gradedSubjects->sum('points');
        $subjectCount = $gradedSubjects->count();
        $averagePoints = $subjectCount > 0 ? round($totalPoints / $subjectCount, 2) : 0;
        $overallGrade = AssessmentGradingService::resolve($average, $ranges)['grade'];

        return [
            'format' => 'o-level',
            'format_label' => 'O-Level School Report (Competency-Based)',
            'subjects' => $gradedSubjects,
            // CHANGED: keep legacy values for compatibility, but UI should prefer points fields.
            'total_marks' => $totalMarks,
            'average' => round($average, 2),
            'total_points' => $totalPoints,
            'average_points' => $averagePoints,
            'subject_count' => $subjectCount,
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
        $ranges = AssessmentGradingService::rangesForExam($exam);

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
