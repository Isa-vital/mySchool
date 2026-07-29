<?php

namespace App\Services;

// CHANGED (A3): report-card data assembly extracted from ReportCardController so the
// queued bulk job can reuse it, and class totals can be computed ONCE per class run
// instead of once per student (the old path was O(N²) for a class of N students).

use App\Models\Exam;
use App\Models\ReportCard;
use App\Models\Student;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
        // CHANGED (verification): every rendered report carries a permanent unguessable
        // serial + QR so anyone can confirm it against live records at /verify/{code}.
        if (! $reportCard->verification_code) {
            $reportCard->verification_code = strtoupper(Str::random(16));
            $reportCard->save();
        }
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
            // CHANGED (legend): grading key so the report card prints a self-explanatory
            // legend from the SAME configurable ranges used to grade the marks.
            'gradingKey' => AssessmentGradingService::rangesForExam($exam, $schoolClass),
            // CHANGED (verification): SVG generated server-side so the Blade template
            // stays formatter-safe (it only echoes the ready-made string).
            'verificationQr' => self::verificationQrSvg($reportCard->verification_code),
            'verificationCode' => $reportCard->verification_code,
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
    public static function computeFigures(Student $student, Exam $exam, ?Collection $precomputedClassTotals = null): array    {
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
            // CHANGED (tie ranking): was `$rank = $classTotals->search(...)` which gave tied
            // students the same slot but never skipped the next rank consistently. Standard
            // competition ranking: position = (students with a strictly higher total) + 1,
            // so equal totals share a position and the next rank is skipped (1,2,2,4).
            // $rank = $classTotals->search(fn($t) => (float) $t === (float) $total);
            // $position = $rank === false ? null : $rank + 1;
            $position = $classSize > 0
                ? $classTotals->filter(fn($t) => (float) $t > (float) $total)->count() + 1
                : null;
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

    // CHANGED (verification): render the /verify/{code} URL as an inline SVG QR
    // (bacon-qr-code, pure PHP — no GD/Imagick). Returned without the XML prolog
    // so it can be embedded directly in the PDF body.
    public static function verificationQrSvg(?string $code): ?string
    {
        if (! $code) {
            return null;
        }

        try {
            $renderer = new ImageRenderer(new RendererStyle(90, 0), new SvgImageBackEnd());
            $svg = (new Writer($renderer))->writeString(route('report.verify', $code));

            return preg_replace('/^<\?xml.*?\?>\s*/s', '', $svg);
        } catch (\Throwable $e) {
            // A missing QR must never block report generation.
            return null;
        }
    }
}
