<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use Spatie\Permission\Models\Permission;
use App\Services\AssessmentGradingService;
use App\Services\ReportCardCompositionService;
use App\Services\UgandaGrading;
use App\Services\ReportCardFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    public function index(Request $request)
    {
        // CHANGED: admins can see all exams (published + unpublished) so they can enter
        // report cards before publishing. Other users only see published exams.
        $examsQuery = Exam::with(['academicYear', 'term'])->orderBy('created_at', 'desc');

        $user = auth()->user();
        $canEditReportCards = false;
        if ($user) {
            // CHANGED: in some environments this permission might not exist yet.
            // hasPermissionTo throws when the permission record is missing.
            $hasPermissionDefinition = Permission::where('name', 'report_cards.edit')->where('guard_name', 'web')->exists();
            $canEditReportCards = $hasPermissionDefinition ? $user->hasPermissionTo('report_cards.edit') : false;
        }

        if (!$user?->hasRole('Super Admin') && !$canEditReportCards) {
            $examsQuery->where('is_published', true);
        }

        $exams = $examsQuery->get();
        $classes = SchoolClass::active()->orderBy('level')->get();

        $students = collect();
        if ($request->filled('class_id') && $request->filled('exam_id')) {
            $exam = Exam::find($request->exam_id);
            if ($exam) {
                $students = Student::whereHas('enrollments', function ($q) use ($request, $exam) {
                    $q->where('school_class_id', $request->class_id)
                        ->where('academic_year_id', $exam->academic_year_id)
                        ->where('status', 'active');
                })->orderBy('first_name')->get();
            }
        }

        return view('report-cards.index', compact('exams', 'classes', 'students'));
    }

    public function show(Student $student, Exam $exam)
    {
        return view('report-cards.show', $this->buildReportData($student, $exam));
    }

    public function update(Request $request, Student $student, Exam $exam)
    {
        // CHANGED: defense-in-depth authorization in addition to route middleware.
        $user = $request->user();
        $hasPermissionDefinition = Permission::where('name', 'report_cards.edit')->where('guard_name', 'web')->exists();
        $canEdit = $user && ($user->hasRole('Super Admin') || ($hasPermissionDefinition && $user->hasPermissionTo('report_cards.edit')));
        abort_unless($canEdit, 403);

        $validated = $request->validate([
            'conduct' => 'nullable|string|max:100',
            'class_teacher_comment' => 'nullable|string|max:1000',
            'head_teacher_comment' => 'nullable|string|max:1000',
            'next_term_begins' => 'nullable|date',
        ]);

        // Recompute the cached figures so the stored card stays consistent.
        $computed = $this->computeFigures($student, $exam);

        ReportCard::updateOrCreate(
            ['student_id' => $student->id, 'exam_id' => $exam->id],
            array_merge($validated, $computed)
        );

        return redirect()
            ->route('report-cards.show', ['student' => $student->id, 'exam' => $exam->id])
            ->with('success', 'Report card remarks saved.');
    }

    public function pdf(Student $student, Exam $exam)
    {
        $pdf = Pdf::loadView('report-cards.pdf', $this->buildReportData($student, $exam));
        return $pdf->download("report-card-{$student->admission_number}-{$exam->name}.pdf");
    }

    /**
     * Assemble everything a report card view needs: grades, totals, class
     * position, Uganda national result (PLE/UCE/UACE) and stored remarks.
     */
    protected function buildReportData(Student $student, Exam $exam): array
    {
        $enrollment = $student->enrollments()->where('academic_year_id', $exam->academic_year_id)->first();
        $schoolClass = $enrollment?->schoolClass;

        $figures = $this->computeFigures($student, $exam);
        $grades = $figures['grades'];
        $reportCard = ReportCard::firstOrNew(['student_id' => $student->id, 'exam_id' => $exam->id]);
        $componentExams = ReportCardCompositionService::componentExams($exam);

        // Format report card based on assessment format
        $formatted = ReportCardFormatter::format(
            $exam,
            $grades,
            $figures['total_marks'],
            $figures['average'],
            $figures['position'],
            $figures['class_size']
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
            'formatted' => $formatted, // New formatted data
        ];
    }

    /**
     * Compute totals, class position and national result for one student/exam.
     */
    protected function computeFigures(Student $student, Exam $exam): array
    {
        $enrollment = $student->enrollments()->where('academic_year_id', $exam->academic_year_id)->first();
        $schoolClass = $enrollment?->schoolClass;

        $composed = ReportCardCompositionService::buildStudentFigures($student, $exam, $schoolClass?->id);
        $grades = $composed['grades'];
        $marks = $grades->pluck('marks_obtained')->filter(fn($m) => $m !== null)->map(fn($m) => (float) $m)->all();

        $total = $composed['total_marks'];
        $average = $composed['average'];

        // Class position: rank every student in the same class/exam by total marks.
        $position = null;
        $classSize = null;
        if ($schoolClass) {
            $classTotals = ReportCardCompositionService::classTotals($exam, $schoolClass->id, $exam->academic_year_id)
                ->sortDesc()
                ->values();

            $classSize = $classTotals->count();
            $rank = $classTotals->search(fn($t) => (float) $t === (float) $total);
            $position = $rank === false ? null : $rank + 1;
        }

        // Uganda national result (only for P.7 / S.4 / S.6).
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
