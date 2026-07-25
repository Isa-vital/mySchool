<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Term;
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

        // CHANGED: keep exams list for backward compatibility/reference, but UI uses term selector.
        $exams = $examsQuery->get();
        $terms = Term::query()
            ->whereHas('academicYear')
            ->orderByDesc('start_date')
            ->get();
        $classes = SchoolClass::active()->orderBy('level')->get();
        $selectedExam = null;

        // CHANGED (UX): default to the current term so users only pick the class.
        $selectedTermId = $request->get('term_id', Term::current()?->id);

        $students = collect();
        if ($request->filled('class_id') && $selectedTermId) {
            $examForTermQuery = Exam::query()
                ->where('term_id', $selectedTermId)
                ->orderByDesc('is_report_card')
                ->orderByDesc('is_published')
                ->orderByDesc('created_at');

            if (!$user?->hasRole('Super Admin') && !$canEditReportCards) {
                $examForTermQuery->where('is_published', true);
            }

            // Prefer designated composite report exam for the selected term.
            $selectedExam = (clone $examForTermQuery)->where('is_report_card', true)->first();

            // CHANGED: fallback to latest exam in term if no report exam is flagged yet.
            if (!$selectedExam) {
                $selectedExam = $examForTermQuery->first();
            }

            if ($selectedExam) {
                $students = Student::whereHas('enrollments', function ($q) use ($request, $selectedExam) {
                    $q->where('school_class_id', $request->class_id)
                        ->where('academic_year_id', $selectedExam->academic_year_id)
                        ->where('status', 'active');
                })->orderBy('first_name')->get();
            }
        }

        return view('report-cards.index', compact('exams', 'terms', 'classes', 'students', 'selectedExam', 'selectedTermId'));
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
     * CHANGED (UX): bulk comments editor — edit conduct/comments for a whole class
     * on one page instead of opening every student's report card individually.
     */
    public function bulkComments(Request $request, Exam $exam)
    {
        $request->validate(['class_id' => 'required|integer|exists:school_classes,id']);

        $schoolClass = SchoolClass::findOrFail($request->class_id);
        $students = $this->classStudents($exam, (int) $request->class_id);
        $reportCards = ReportCard::where('exam_id', $exam->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        // Suggest the most common existing next-term date as the shared default.
        $nextTermBegins = $reportCards->pluck('next_term_begins')->filter()->countBy(fn($d) => $d->format('Y-m-d'))->sortDesc()->keys()->first();

        return view('report-cards.bulk-comments', compact('exam', 'schoolClass', 'students', 'reportCards', 'nextTermBegins'));
    }

    /**
     * CHANGED (UX): save bulk comments. Only rows with any content are written.
     */
    public function saveBulkComments(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'class_id' => 'required|integer|exists:school_classes,id',
            'next_term_begins' => 'nullable|date',
            'rows' => 'array',
            'rows.*.conduct' => 'nullable|string|max:100',
            'rows.*.class_teacher_comment' => 'nullable|string|max:1000',
            'rows.*.head_teacher_comment' => 'nullable|string|max:1000',
        ]);

        $students = $this->classStudents($exam, (int) $validated['class_id'])->keyBy('id');
        $saved = 0;

        foreach (($validated['rows'] ?? []) as $studentId => $row) {
            $student = $students->get((int) $studentId);
            if (! $student) {
                continue; // ignore rows for students not in this class/exam
            }

            $payload = array_filter([
                'conduct' => $row['conduct'] ?? null,
                'class_teacher_comment' => $row['class_teacher_comment'] ?? null,
                'head_teacher_comment' => $row['head_teacher_comment'] ?? null,
                'next_term_begins' => $validated['next_term_begins'] ?? null,
            ], fn($v) => $v !== null && $v !== '');

            $existing = ReportCard::where('student_id', $student->id)->where('exam_id', $exam->id)->first();
            if ($payload === [] && ! $existing) {
                continue; // nothing to store for this student
            }

            // Recompute cached figures so the stored card stays consistent (same as single update).
            $computed = $this->computeFigures($student, $exam);

            ReportCard::updateOrCreate(
                ['student_id' => $student->id, 'exam_id' => $exam->id],
                array_merge($payload, $computed)
            );
            $saved++;
        }

        return redirect()
            ->route('report-cards.bulk-comments', ['exam' => $exam->id, 'class_id' => $validated['class_id']])
            ->with('success', "Comments saved for {$saved} student(s).");
    }

    /**
     * CHANGED (A3): bulk PDF is now a QUEUED job (GenerateBulkReportCardsJob) — the old
     * synchronous loop recomputed class totals per student (O(N²)) and risked request
     * timeouts on classes of 40+. The UI polls bulkPdfStatus() for progress.
     */
    public function bulkPdf(Request $request, Exam $exam)
    {
        $request->validate(['class_id' => 'required|integer|exists:school_classes,id']);

        $schoolClass = SchoolClass::findOrFail($request->class_id);
        $students = $this->classStudents($exam, (int) $request->class_id);
        abort_if($students->isEmpty(), 404, 'No enrolled students found for this class.');

        $progressKey = \Illuminate\Support\Str::random(16);

        \App\Jobs\GenerateBulkReportCardsJob::dispatch($exam->id, (int) $request->class_id, (int) $request->user()->id, $progressKey);

        return response()->json([
            'success' => true,
            'key' => $progressKey,
            'total' => $students->count(),
            'status_url' => route('report-cards.bulk-pdf.status', ['exam' => $exam->id, 'key' => $progressKey]),
        ]);
    }

    /**
     * CHANGED (A3): progress endpoint polled by the report-cards page.
     */
    public function bulkPdfStatus(Request $request, Exam $exam)
    {
        $request->validate(['key' => 'required|string']);

        $state = \Illuminate\Support\Facades\Cache::get(
            \App\Jobs\GenerateBulkReportCardsJob::progressCacheKey($request->key)
        );

        return response()->json($state ?? ['status' => 'pending', 'done' => 0, 'total' => null, 'url' => null]);
    }

    /**
     * Active students enrolled in a class for the exam's academic year.
     */
    protected function classStudents(Exam $exam, int $classId)
    {
        return Student::whereHas('enrollments', function ($q) use ($classId, $exam) {
            $q->where('school_class_id', $classId)
                ->where('academic_year_id', $exam->academic_year_id)
                ->where('status', 'active');
        })->orderBy('first_name')->get();
    }

    /**
     * Assemble everything a report card view needs: grades, totals, class
     * position, Uganda national result (PLE/UCE/UACE) and stored remarks.
     * CHANGED (A3): logic moved to ReportCardDataService (shared with the queued
     * bulk job); this thin wrapper keeps existing call sites working.
     */
    protected function buildReportData(Student $student, Exam $exam): array
    {
        return \App\Services\ReportCardDataService::buildReportData($student, $exam);
    }

    /**
     * Compute totals, class position and national result for one student/exam.
     * CHANGED (A3): moved to ReportCardDataService — see buildReportData().
     */
    protected function computeFigures(Student $student, Exam $exam): array
    {
        return \App\Services\ReportCardDataService::computeFigures($student, $exam);
    }
}
