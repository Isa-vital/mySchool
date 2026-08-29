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
            $schoolClass,
            $enrollment, // CHANGED (A-Level rebuild): carries the UACE combination
            $figures['uace'] // CHANGED (UACE paper rebuild): paper-level result when present
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
        // CHANGED (UACE paper rebuild): when per-paper results exist for this sitting,
        // the paper-level engine is authoritative for grading and ranking.
        $uace = null;
        $provisional = false;
        if ($schoolClass && $schoolClass->category() === 'a_level' && $enrollment) {
            $sitting = UaceGradingEngine::resolveSitting($exam, $enrollment->id);
            if ($sitting) {
                $candidate = UaceGradingEngine::studentResult($enrollment, $sitting);
                if ($candidate['available']) {
                    $uace = $candidate;
                    $provisional = $candidate['provisional'];
                }
            }
        }
        if ($schoolClass) {
            $classTotals = ($precomputedClassTotals
                ?? ReportCardCompositionService::classTotals($exam, $schoolClass->id, $exam->academic_year_id))
                ->sortDesc()
                ->values();

            // CHANGED (A-Level rebuild): S.5/S.6 rank by UACE points (marks as tie-break),
            // matching the metric classTotals() produces for a-level classes.
            $rankValue = (float) $total;
            if ($uace) {
                $rankValue = $uace['total_points'] * 10000 + (float) $uace['marks_total'];
            } elseif ($schoolClass->category() === 'a_level') {
                $combination = $enrollment?->subjectCombination;
                $combination?->loadMissing('subjects');
                $ranges = AssessmentGradingService::rangesForExam($exam, $schoolClass);
                $breakdown = UgandaGrading::uaceBreakdown($grades, $combination, $ranges);
                $rankValue = $breakdown['total_points'] * 10000 + (float) $total;
            }

            $classSize = $classTotals->count();
            // CHANGED (tie ranking): was `$rank = $classTotals->search(...)` which gave tied
            // students the same slot but never skipped the next rank consistently. Standard
            // competition ranking: position = (students with a strictly higher total) + 1,
            // so equal totals share a position and the next rank is skipped (1,2,2,4).
            // $rank = $classTotals->search(fn($t) => (float) $t === (float) $total);
            // $position = $rank === false ? null : $rank + 1;
            $position = $classSize > 0
                ? $classTotals->filter(fn($t) => (float) $t > $rankValue)->count() + 1
                : null;

            // CHANGED (UACE paper rebuild): provisional students are not ranked —
            // an INCOMPLETE/UNMATCHED subject must never silently affect positions.
            if ($provisional) {
                $position = null;
            }
        }

        // Uganda national result (only for P.7 / S.4 / S.6).
        // S.4 now reports the competency-based achievement level — the legacy
        // stanine/aggregate/division path was retired for UCE (CBC transition).
        $national = UgandaGrading::nationalResult($schoolClass?->nationalExam(), $marks);

        return [
            'grades' => $grades,
            'total_marks' => $total,
            'average' => $average,
            'position' => $position,
            'class_size' => $classSize,
            'result' => $national['label'],
            'aggregate' => $national['aggregate'],
            // CHANGED (UACE paper rebuild): paper-level result (null = legacy blended term)
            'uace' => $uace,
            'provisional' => $provisional,
        ];
    }

    // CHANGED (verification): render the /verify/{code} URL as a QR image.
    // GD PNG (data URI) is preferred - DomPDF renders raster images reliably on any
    // server. Falls back to inline SVG (needs ext-xmlwriter) when GD is unavailable.
    public static function verificationQrSvg(?string $code): ?string
    {
        if (! $code) {
            return null;
        }

        $url = route('report.verify', $code);

        if (extension_loaded('gd')) {
            try {
                $png = (new Writer(new \BaconQrCode\Renderer\GDLibRenderer(180)))->writeString($url);

                return '<img src="data:image/png;base64,' . base64_encode($png) . '" style="width:70px; height:70px;" alt="Verification QR">';
            } catch (\Throwable $e) {
                // fall through to SVG
            }
        }

        try {
            $renderer = new ImageRenderer(new RendererStyle(90, 0), new SvgImageBackEnd());
            $svg = (new Writer($renderer))->writeString($url);

            return preg_replace('/^<\?xml.*?\?>\s*/s', '', $svg);
        } catch (\Throwable $e) {
            // A missing QR must never block report generation.
            return null;
        }
    }
}
