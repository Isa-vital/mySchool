<?php

namespace App\Services;

// CHANGED (A3): report-card data assembly extracted from ReportCardController so the
// queued bulk job can reuse it, and class totals can be computed ONCE per class run
// instead of once per student (the old path was O(N²) for a class of N students).

use App\Models\Exam;
use App\Models\ReportCard;
use App\Models\Student;
use Illuminate\Support\Collection;

class ReportCardDataService
{
    /**
     * Assemble everything a report card view needs: grades, totals, class
     * position, Uganda national result (PLE/UCE/UACE) and stored remarks.
     *
     * @param Collection|null $precomputedClassTotals student_id => total, computed once per class run
     */
    public static function buildReportData(Student $student, Exam $exam, ?Collection $precomputedClassTotals = null): array
    {
        $enrollment = $student->enrollments()->where('academic_year_id', $exam->academic_year_id)->first();
        $schoolClass = $enrollment?->schoolClass;

        $figures = self::computeFigures($student, $exam, $precomputedClassTotals);
        $grades = $figures['grades'];
        $reportCard = ReportCard::firstOrNew(['student_id' => $student->id, 'exam_id' => $exam->id]);
        $componentExams = ReportCardCompositionService::componentExams($exam);

        // Format report card based on assessment format; the student's class resolves
        // 'auto' to primary (P.1-P.7), o-level (S.1-S.4) or a-level (S.5-S.6).
        $formatted = ReportCardFormatter::format(
            $exam,
            $grades,
            $figures['total_marks'],
            $figures['average'],
            $figures['position'],
            $figures['class_size'],
            $schoolClass
        );

        return [
            'student' => $student,
            'exam' => $exam,
            'grades' => $grades,
            'enrollment' => $enrollment,
            'schoolClass' => $schoolClass,
            'reportCard' => $reportCard,
            'componentExams' => $componentExams,
            'totalMarks' => $figures['total_marks'],
            'average' => $figures['average'],
            'position' => $figures['position'],
            'classSize' => $figures['class_size'],
            'nationalExam' => $schoolClass?->nationalExam(),
            'result' => $figures['result'],
            'aggregate' => $figures['aggregate'],
            'formatted' => $formatted,
        ];
    }

    /**
     * Compute totals, class position and national result for one student/exam.
     */
    public static function computeFigures(Student $student, Exam $exam, ?Collection $precomputedClassTotals = null): array
    {
        $enrollment = $student->enrollments()->where('academic_year_id', $exam->academic_year_id)->first();
        $schoolClass = $enrollment?->schoolClass;

        $composed = ReportCardCompositionService::buildStudentFigures($student, $exam, $schoolClass?->id);
        $grades = $composed['grades'];
        $marks = $grades->pluck('marks_obtained')->filter(fn($m) => $m !== null)->map(fn($m) => (float) $m)->all();

        $total = $composed['total_marks'];
        $average = $composed['average'];

        // Class position: rank every student in the same class/exam by total marks.
        // CHANGED (A3): callers processing a whole class pass the totals in once
        // instead of recomputing them for every student.
        $position = null;
        $classSize = null;
        if ($schoolClass) {
            $classTotals = ($precomputedClassTotals
                ?? ReportCardCompositionService::classTotals($exam, $schoolClass->id, $exam->academic_year_id))
                ->sortDesc()
                ->values();

            $classSize = $classTotals->count();
            $rank = $classTotals->search(fn($t) => (float) $t === (float) $total);
            $position = $rank === false ? null : $rank + 1;
        }

        // Uganda national result (only for P.7 / S.4 / S.6).
        // TODO: pending decision on S.4 projection feature — for S.4 this currently
        // returns the LEGACY stanine-based UCE division, which is outdated post-2020.
        $national = UgandaGrading::nationalResult($schoolClass?->nationalExam(), $marks);

        return [
            'grades' => $grades,
            'total_marks' => $total,
            'average' => $average,
            'position' => $position,
            'class_size' => $classSize,
            'result' => $national['label'],
            'aggregate' => $national['aggregate'],
        ];
    }
}
