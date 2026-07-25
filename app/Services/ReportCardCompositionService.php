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
            // CHANGED (A6): combine weighted subject components (Paper 1/2 …) into one
            // subject score before display.
            return self::combineSubjectRows(
                Grade::with(['subject', 'subjectComponent'])
                    ->where('student_id', $student->id)
                    ->where('exam_id', $exam->id)
                    ->get()
            );
        }

        $weightTotal = self::totalWeight($components);
        $subjects = [];

        foreach ($components as $component) {
            $weight = (float) ($component->pivot->weight ?? 0);
            // CHANGED (A6): combine weighted subject components before exam-level weighting.
            $grades = self::combineSubjectRows(
                Grade::with(['subject', 'subjectComponent'])
                    ->where('student_id', $student->id)
                    ->where('exam_id', $component->id)
                    ->when($schoolClassId, fn($query) => $query->where('school_class_id', $schoolClassId))
                    ->get()
            );

            $fullMarksBySubject = ExamSchedule::query()
                ->where('exam_id', $component->id)
                ->when($schoolClassId, fn($query) => $query->where('school_class_id', $schoolClassId))
                ->pluck('full_marks', 'subject_id');

            foreach ($grades as $grade) {
                $subjectId = (int) $grade->subject_id;
                // CHANGED (A6): combined component scores are already percentages (0-100).
                $isCombined = (bool) ($grade->is_combined ?? false);
                $fullMarks = $isCombined ? 100.0 : (float) ($fullMarksBySubject[$subjectId] ?? $component->max_points ?? 100);
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

    /**
     * CHANGED (A6): collapse per-component grade rows (Paper 1/2, theory + practical)
     * into one weighted percentage per subject. Rows without a subject_component_id
     * pass through untouched (legacy single-score subjects).
     */
    public static function combineSubjectRows(Collection $grades): Collection
    {
        return $grades
            ->groupBy('subject_id')
            ->map(function (Collection $rows) {
                $componentRows = $rows->filter(fn($g) => $g->subject_component_id !== null);

                // Legacy: a single whole-subject score.
                if ($componentRows->isEmpty()) {
                    return $rows->first();
                }

                $weightTotal = (float) $componentRows->sum(fn($g) => (float) ($g->subjectComponent->weight ?? 1));
                $weightTotal = $weightTotal > 0 ? $weightTotal : (float) $componentRows->count();

                $weighted = 0.0;
                $parts = [];
                foreach ($componentRows as $row) {
                    $component = $row->subjectComponent;
                    $maxScore = (float) ($component->max_score ?? 100) ?: 100;
                    $componentWeight = (float) ($component->weight ?? 1) ?: 1;
                    $weighted += (((float) $row->marks_obtained) / $maxScore) * $componentWeight;
                    $parts[] = [
                        'component_id' => $component->id ?? null,
                        'name' => $component->name ?? '',
                        'marks' => (float) $row->marks_obtained,
                        'max_score' => $maxScore,
                        'weight' => $componentWeight,
                    ];
                }

                $first = $componentRows->first();

                return (object) [
                    'subject' => $first->subject,
                    'subject_id' => (int) $first->subject_id,
                    'marks_obtained' => round(($weighted / $weightTotal) * 100, 2),
                    'is_combined' => true,
                    'paper_components' => $parts,
                    'remarks' => $first->remarks,
                ];
            })
            ->values();
    }
}
