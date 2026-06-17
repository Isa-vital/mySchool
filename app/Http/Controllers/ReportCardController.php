<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\SchoolClass;
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
        
        if (!auth()->user()?->hasRole('Super Admin') && !auth()->user()?->hasPermissionTo('report_cards.edit')) {
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
        $grades = Grade::with('subject')
            ->where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->get();

        $enrollment = $student->enrollments()->where('academic_year_id', $exam->academic_year_id)->first();
        $schoolClass = $enrollment?->schoolClass;

        $figures = $this->computeFigures($student, $exam);
        $reportCard = ReportCard::firstOrNew(['student_id' => $student->id, 'exam_id' => $exam->id]);

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

        $grades = Grade::where('student_id', $student->id)->where('exam_id', $exam->id)->get();
        $marks = $grades->pluck('marks_obtained')->filter(fn($m) => $m !== null)->map(fn($m) => (float) $m)->all();

        $total = array_sum($marks);
        $count = count($marks);
        $average = $count > 0 ? round($total / $count, 2) : 0;

        // Class position: rank every student in the same class/exam by total marks.
        $position = null;
        $classSize = null;
        if ($schoolClass) {
            $classTotals = Grade::query()
                ->where('exam_id', $exam->id)
                ->where('school_class_id', $schoolClass->id)
                ->selectRaw('student_id, SUM(marks_obtained) as total')
                ->groupBy('student_id')
                ->pluck('total', 'student_id')
                ->sortDesc()
                ->values();

            $classSize = $classTotals->count();
            $rank = $classTotals->search(fn($t) => (float) $t === (float) $total);
            $position = $rank === false ? null : $rank + 1;
        }

        // Uganda national result (only for P.7 / S.4 / S.6).
        $national = UgandaGrading::nationalResult($schoolClass?->nationalExam(), $marks);

        return [
            'total_marks' => $total,
            'average' => $average,
            'position' => $position,
            'class_size' => $classSize,
            'result' => $national['label'],
            'aggregate' => $national['aggregate'],
        ];
    }
}
