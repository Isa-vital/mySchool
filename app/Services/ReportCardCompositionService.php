<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Support\Collection;

class ReportCardCompositionService
{
    public static function componentExams(Exam $exam): Collection
    {
        $components = $exam->relationLoaded('reportComponents')
            ? $exam->reportComponents
            : $exam->reportComponents()->get();

        if (($exam->is_report_card ?? false) && $components->isNotEmpty()) {
            return $components;
        }

        return collect([$exam]);
    }

    public static function isComposite(Exam $exam): bool
    {
        return ($exam->is_report_card ?? false) && self::componentExams($exam)->isNotEmpty();
    }

    public static function buildStudentGrades(Student $student, Exam $exam, ?int $schoolClassId = null): Collection
    {
        $components = self::componentExams($exam);

        if (! ($exam->is_report_card ?? false) || $components->count() === 1 && (int) $components->first()->id === (int) $exam->id) {
            return Grade::with('subject')
                ->where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->get();
        }

        $weightTotal = self::totalWeight($components);
        $subjects = [];

        foreach ($components as $component) {
            $weight = (float) ($component->pivot->weight ?? 0);
            $grades = Grade::with('subject')
                ->where('student_id', $student->id)
                ->where('exam_id', $component->id)
                ->when($schoolClassId, fn($query) => $query->where('school_class_id', $schoolClassId))
                ->get();

            $fullMarksBySubject = ExamSchedule::query()
                ->where('exam_id', $component->id)
                ->when($schoolClassId, fn($query) => $query->where('school_class_id', $schoolClassId))
                ->pluck('full_marks', 'subject_id');

            foreach ($grades as $grade) {
                $subjectId = (int) $grade->subject_id;
                $fullMarks = (float) ($fullMarksBySubject[$subjectId] ?? $component->max_points ?? 100);
                $fullMarks = $fullMarks > 0 ? $fullMarks : 100;

                if (! isset($subjects[$subjectId])) {
                    $subjects[$subjectId] = [
                        'subject' => $grade->subject,
                        'weighted_total' => 0.0,
                        'components' => [],
                    ];
                }

                $subjects[$subjectId]['weighted_total'] += (((float) $grade->marks_obtained) / $fullMarks) * $weight;
                $subjects[$subjectId]['components'][] = [
                    'exam_id' => $component->id,
                    'exam_name' => $component->name,
                    'weight' => $weight,
                    'marks' => (float) $grade->marks_obtained,
                    'full_marks' => $fullMarks,
                ];
            }
        }

        return collect($subjects)->map(function ($row) use ($weightTotal) {
            $finalMarks = $weightTotal > 0 ? round(($row['weighted_total'] / $weightTotal) * 100, 2) : 0;

            return (object) [
                'subject' => $row['subject'],
                'marks_obtained' => $finalMarks,
                'components' => $row['components'],
            ];
        })->sortBy(fn($grade) => $grade->subject->name)->values();
    }

    public static function buildStudentFigures(Student $student, Exam $exam, ?int $schoolClassId = null): array
    {
        $grades = self::buildStudentGrades($student, $exam, $schoolClassId);
        $marks = $grades->pluck('marks_obtained')->map(fn($mark) => (float) $mark)->all();
        $total = round(array_sum($marks), 2);
        $count = count($marks);

        return [
            'grades' => $grades,
            'total_marks' => $total,
            'average' => $count > 0 ? round($total / $count, 2) : 0.0,
            'subject_count' => $count,
        ];
    }

    public static function classTotals(Exam $exam, int $schoolClassId, int $academicYearId): Collection
    {
        $studentIds = Enrollment::query()
            ->where('school_class_id', $schoolClassId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->pluck('student_id');

        return Student::whereIn('id', $studentIds)->get()->mapWithKeys(function ($student) use ($exam, $schoolClassId) {
            $figures = self::buildStudentFigures($student, $exam, $schoolClassId);
            return [$student->id => $figures['total_marks']];
        });
    }

    public static function totalWeight(Collection $components): float
    {
        $weightTotal = (float) $components->sum(fn($component) => (float) ($component->pivot->weight ?? 0));

        return $weightTotal > 0 ? $weightTotal : (float) max($components->count(), 1);
    }
}
