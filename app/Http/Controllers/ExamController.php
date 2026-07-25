<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\GradingScale;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Http\Requests\StoreExamScheduleRequest;
use App\Mail\ExamResultsPublishedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        // CHANGED (UX): exams list is now filterable by term/type and defaults to the
        // current academic year so old exams stop cluttering the list.
        // $exams = Exam::with(['academicYear', 'term'])->orderBy('created_at', 'desc')->paginate(20);
        $currentYear = AcademicYear::current();

        $examsQuery = Exam::with(['academicYear', 'term'])->orderBy('created_at', 'desc');

        $selectedTermId = $request->get('term_id');
        $selectedYearId = $request->get('academic_year_id', $request->has('term_id') ? null : $currentYear?->id);

        if ($selectedTermId) {
            $examsQuery->where('term_id', $selectedTermId);
        } elseif ($selectedYearId && $selectedYearId !== 'all') {
            $examsQuery->where('academic_year_id', $selectedYearId);
        }

        if ($request->filled('type')) {
            $examsQuery->where('is_report_card', $request->type === 'report');
        }

        $exams = $examsQuery->paginate(20)->withQueryString();
        $academicYears = AcademicYear::with('terms')->orderBy('start_date', 'desc')->get();

        return view('exams.index', compact('exams', 'academicYears', 'selectedYearId', 'selectedTermId'));
    }

    public function create()
    {
        $academicYears = AcademicYear::with('terms')->orderBy('start_date', 'desc')->get();
        $classes = SchoolClass::active()->orderBy('level')->get();
        $gradingScales = GradingScale::orderBy('name')->get();
        $availableComponentExams = Exam::with(['academicYear', 'term'])
            ->orderBy('start_date', 'desc')
            ->orderBy('name')
            ->get();

        // CHANGED (UX): pre-select the current academic year & term so users don't
        // have to pick them on every exam.
        $currentYear = AcademicYear::current();
        $currentTerm = Term::current();

        // CHANGED (permanent formatter fix): ALL data used by the view's <script> block is
        // prepared here. A code formatter twice destroyed the Blade views by reformatting
        // PHP expressions inside @php/<script>; plain variables passed to @json are immune.
        $scriptData = $this->examFormScriptData($academicYears, $availableComponentExams);
        $scriptData['preselectedTermId'] = (string) old('term_id', $currentTerm?->id ?? '');
        $scriptData['savedComponentsData'] = collect(old('report_components', []))
            ->filter(fn($r) => ($r['exam_id'] ?? '') !== '')->values();

        return view('exams.create', array_merge(
            compact('academicYears', 'classes', 'gradingScales', 'availableComponentExams', 'currentYear', 'currentTerm'),
            $scriptData
        ));
    }

    /**
     * Shared script payload for the exam create/edit forms.
     */
    protected function examFormScriptData($academicYears, $availableComponentExams): array
    {
        return [
            'termsByYearData' => $academicYears->mapWithKeys(fn($y) => [
                $y->id => $y->terms->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->values(),
            ]),
            'examOptionsData' => $availableComponentExams->map(fn($e) => [
                'id' => $e->id,
                'label' => $e->name . ' - ' . ($e->term->name ?? 'No Term') . ' / ' . ($e->academicYear->name ?? 'No Year'),
            ])->values(),
        ];
    }

    /**
     * CHANGED (UX): term setup wizard — creates a whole term's exam sets (BOT/MID/END),
     * their schedules for all classes, and the composite report exam in ONE submission.
     */
    public function termSetup()
    {
        $academicYears = AcademicYear::with('terms')->orderBy('start_date', 'desc')->get();
        $classes = SchoolClass::active()->orderBy('level')->get();
        $currentTerm = Term::current();

        // CHANGED (QA fix): the wizard matches exams BY NAME, so the form prefills each
        // term's EXISTING exams (e.g. BOT/MOT/EOT) instead of static defaults — otherwise
        // submitting would silently create empty duplicates and link the report card to
        // exams that have no marks.
        $reportsByTerm = Exam::where('is_report_card', true)->with('reportComponents')->get()->groupBy('term_id');

        $componentWeights = [];
        foreach ($reportsByTerm as $termId => $reports) {
            foreach ($reports->first()->reportComponents as $component) {
                $componentWeights[$termId][$component->id] = (float) $component->pivot->weight;
            }
        }

        $termPrefillData = Exam::where('is_report_card', false)
            ->orderBy('created_at')
            ->get()
            ->groupBy('term_id')
            ->map(function ($exams, $termId) use ($componentWeights, $reportsByTerm) {
                return [
                    'sets' => $exams->map(fn($e) => [
                        'name' => $e->name,
                        'weight' => $componentWeights[$termId][$e->id] ?? '',
                    ])->values(),
                    'report_name' => $reportsByTerm->get($termId)?->first()?->name,
                ];
            });

        // Old input (after a validation failure) wins over the prefill.
        $oldSetsData = collect(old('sets', []))->values();

        return view('exams.term-setup', compact('academicYears', 'classes', 'currentTerm', 'termPrefillData', 'oldSetsData'));
    }

    public function storeTermSetup(Request $request)
    {
        $validated = $request->validate([
            'term_id' => 'required|integer|exists:terms,id',
            'sets' => 'required|array|min:1',
            'sets.*.name' => 'nullable|string|max:255',
            'sets.*.weight' => 'nullable|numeric|min:0',
            'create_report' => 'nullable|boolean',
            'report_name' => 'nullable|string|max:255',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'integer',
        ]);

        // CHANGED (QA fix): all-blank set names previously "succeeded" with a confusing
        // "0 exam set(s) ready" — now it's a proper validation error.
        if (collect($validated['sets'])->filter(fn($s) => trim((string) ($s['name'] ?? '')) !== '')->isEmpty()) {
            return back()->withInput()->withErrors(['sets' => 'Name at least one exam set.']);
        }

        $term = Term::findOrFail($validated['term_id']);

        $result = \App\Services\ExamSetupService::setupTerm(
            $term,
            $validated['sets'],
            $request->boolean('create_report'),
            $validated['report_name'] ?? null,
            array_map('intval', $validated['class_ids'] ?? [])
        );

        $summary = count($result['components']) . ' exam set(s) ready, ' . $result['schedules'] . ' schedule(s) created'
            . ($result['report'] ? ', report card exam "' . $result['report']->name . '" linked.' : '.');

        $target = $result['report'] ?? ($result['components'][0]['exam'] ?? null);

        return $target
            ? redirect()->route('exams.show', $target)->with('success', "Term setup complete — {$summary}")
            : redirect()->route('exams.index')->with('success', "Term setup complete — {$summary}");
    }

    public function store(StoreExamRequest $request)
    {
        $validated = $request->validated();

        // CHANGED (A1): 'auto' is no longer stored — auto-detect is NULL at the DB layer
        // and the effective format is resolved at read time from the student's class.
        // $validated['assessment_format'] = $validated['assessment_format'] ?? setting('report_card_format', 'auto');
        $validated['assessment_format'] = $this->normalizeAssessmentFormat($validated['assessment_format'] ?? null);
        $validated['max_points'] = $validated['max_points'] ?? 100;
        $validated['is_report_card'] = $request->boolean('is_report_card');

        $exam = Exam::create($validated);
        $this->syncReportComponents($exam, $validated['report_components'] ?? []);

        // CHANGED: composite report-card exams are computed from component exams,
        // so they do not get direct subject schedules.
        if (! $exam->is_report_card && $request->filled('class_ids')) {
            foreach ($request->class_ids as $classId) {
                $class = SchoolClass::with('subjects')->find($classId);
                if ($class) {
                    foreach ($class->subjects as $subject) {
                        ExamSchedule::create([
                            'exam_id' => $exam->id,
                            'school_class_id' => $classId,
                            'subject_id' => $subject->id,
                            'full_marks' => 100,
                            'pass_marks' => 40,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('exams.show', $exam)->with('success', 'Exam created successfully.');
    }

    public function show(Exam $exam)
    {
        $exam->load(['academicYear', 'term', 'schedules.schoolClass', 'schedules.subject', 'grades', 'gradingScale', 'reportComponents.academicYear', 'reportComponents.term']);
        $classes = SchoolClass::active()->orderBy('level')->get();
        $subjects = Subject::active()->orderBy('name')->get();

        // CHANGED (UX): marks completeness matrix — shows entered/enrolled counts per
        // class & subject with direct links into the right entry form, so users can see
        // at a glance what still needs marks instead of tracking it manually.
        $completeness = $this->buildCompleteness($exam);

        return view('exams.show', compact('exam', 'classes', 'subjects', 'completeness'));
    }

    /**
     * Build a class → subject completeness matrix for an exam.
     * Regular exams count their own grades; composite report exams aggregate
     * distinct students graded across their component exams.
     */
    protected function buildCompleteness(Exam $exam): array
    {
        $examIds = $exam->is_report_card
            ? $exam->reportComponents->pluck('id')->all()
            : [$exam->id];

        if (empty($examIds)) {
            return [];
        }

        $enrolledCounts = \App\Models\Enrollment::where('academic_year_id', $exam->academic_year_id)
            ->where('status', 'active')
            ->selectRaw('school_class_id, count(*) as total')
            ->groupBy('school_class_id')
            ->pluck('total', 'school_class_id');

        $gradeCounts = \App\Models\Grade::whereIn('exam_id', $examIds)
            ->selectRaw('school_class_id, subject_id, count(distinct student_id) as entered')
            ->groupBy('school_class_id', 'subject_id')
            ->get()
            ->keyBy(fn($row) => $row->school_class_id . ':' . $row->subject_id);

        // Class/subject pairs: from schedules for regular exams, from active classes'
        // subject assignments for composite report exams.
        $pairsByClass = [];
        if ($exam->is_report_card) {
            $classesWithSubjects = SchoolClass::active()->with('subjects')->orderBy('level')->get();
            foreach ($classesWithSubjects as $class) {
                if (! $enrolledCounts->has($class->id)) {
                    continue; // no enrolled students — nothing to grade
                }
                foreach ($class->subjects as $subject) {
                    $pairsByClass[$class->id]['class'] = $class;
                    $pairsByClass[$class->id]['subjects'][] = $subject;
                }
            }
        } else {
            foreach ($exam->schedules as $schedule) {
                if (! $schedule->schoolClass || ! $schedule->subject) {
                    continue;
                }
                $pairsByClass[$schedule->school_class_id]['class'] = $schedule->schoolClass;
                $pairsByClass[$schedule->school_class_id]['subjects'][] = $schedule->subject;
            }
        }

        $matrix = [];
        foreach ($pairsByClass as $classId => $data) {
            $total = (int) ($enrolledCounts[$classId] ?? 0);
            $row = ['class' => $data['class'], 'total' => $total, 'cells' => []];

            foreach ($data['subjects'] as $subject) {
                $entered = (int) ($gradeCounts[$classId . ':' . $subject->id]->entered ?? 0);
                $row['cells'][] = [
                    'subject' => $subject,
                    'entered' => $entered,
                    'complete' => $total > 0 && $entered >= $total,
                    'url' => $exam->is_report_card
                        ? route('grades.enter-grid', ['report_exam' => $exam->id, 'class_id' => $classId, 'subject_id' => $subject->id])
                        : route('grades.enter', ['exam' => $exam->id, 'class_id' => $classId, 'subject_id' => $subject->id]),
                ];
            }

            $matrix[] = $row;
        }

        return $matrix;
    }

    public function edit(Exam $exam)
    {
        $academicYears = AcademicYear::with('terms')->orderBy('start_date', 'desc')->get();
        $gradingScales = GradingScale::orderBy('name')->get();
        $availableComponentExams = Exam::with(['academicYear', 'term'])
            ->whereKeyNot($exam->id)
            ->orderBy('start_date', 'desc')
            ->orderBy('name')
            ->get();

        // CHANGED (permanent formatter fix): view script data prepared here — see create().
        $scriptData = $this->examFormScriptData($academicYears, $availableComponentExams);
        $scriptData['preselectedTermId'] = (string) old('term_id', $exam->term_id ?? '');
        $scriptData['savedComponentsData'] = collect(old('report_components', []))
            ->filter(fn($r) => ($r['exam_id'] ?? '') !== '')->values();
        $scriptData['existingComponentsData'] = $exam->reportComponents->map(fn($c) => [
            'exam_id' => $c->id,
            'weight' => (float) $c->pivot->weight,
        ])->values();

        return view('exams.edit', array_merge(
            compact('exam', 'academicYears', 'gradingScales', 'availableComponentExams'),
            $scriptData
        ));
    }

    public function update(UpdateExamRequest $request, Exam $exam)
    {
        $validated = $request->validated();

        // CHANGED (A1): 'auto' is stored as NULL (see store()).
        // $validated['assessment_format'] = $validated['assessment_format'] ?? $exam->assessment_format ?? setting('report_card_format', 'auto');
        $validated['assessment_format'] = $this->normalizeAssessmentFormat($validated['assessment_format'] ?? $exam->assessment_format);
        $validated['max_points'] = $validated['max_points'] ?? $exam->max_points ?? 100;
        $validated['is_report_card'] = $request->boolean('is_report_card');

        // CHANGED (A2): publication is no longer toggled from the edit form — it goes
        // through the status workflow (draft → marks_entry_open → locked → published)
        // via updateStatus()/publish(), so editing an exam can't silently (un)publish it
        // or fire guardian emails.
        // $validated['is_published'] = $request->has('is_published');

        $exam->update($validated);
        $this->syncReportComponents($exam, $validated['report_components'] ?? []);

        // CHANGED (A2): guardian notification moved to publish() (status workflow).
        // if ($request->has('is_published') && $exam->is_published) {
        //     $studentIds = $exam->grades()->distinct()->pluck('student_id');
        //     $students = \App\Models\Student::with('guardians')->whereIn('id', $studentIds)->get();
        //     foreach ($students as $student) {
        //         $guardian = $student->primaryGuardian();
        //         $email = $guardian?->email ?? $student->email;
        //         if ($email) {
        //             Mail::to($email)->queue(new ExamResultsPublishedMail($student, $exam));
        //         }
        //     }
        // }

        return redirect()->route('exams.index')->with('success', 'Exam updated successfully.');
    }

    /**
     * CHANGED (A2): explicit status workflow transitions.
     * open: draft → marks_entry_open | lock: marks_entry_open → locked |
     * publish: locked → published | unlock/unpublish: moderator only.
     */
    public function updateStatus(Request $request, Exam $exam)
    {
        $validated = $request->validate(['action' => 'required|string']);

        $transitions = Exam::statusTransitions();
        $transition = $transitions[$validated['action']] ?? null;
        abort_if($transition === null, 422, 'Unknown status action.');

        // CHANGED (bugfix): idempotent — a stale page retrying a transition that already
        // happened (e.g. lock succeeded but the follow-up publish was interrupted) gets a
        // friendly no-op instead of a 422.
        if ($exam->status === $transition['to']) {
            return redirect()->route('exams.show', $exam)->with('success', 'Exam is already "' . str_replace('_', ' ', $transition['to']) . '".');
        }

        abort_unless($exam->status === $transition['from'], 422, "This exam is '{$exam->status}' — cannot {$validated['action']} from that state.");

        if ($transition['moderate']) {
            abort_unless($request->user()->can('exams.moderate'), 403, 'Only a moderator can unlock or unpublish an exam.');
        }

        // Publishing has side effects (guardian emails) — it uses the dedicated
        // Publish button (exams.publish endpoint), not this form action.
        abort_if($validated['action'] === 'publish', 422, 'Use the Publish button to publish results.');

        $exam->update([
            'status' => $transition['to'],
            'is_published' => $transition['to'] === Exam::STATUS_PUBLISHED,
        ]);

        return redirect()->route('exams.show', $exam)->with('success', 'Exam status updated to "' . str_replace('_', ' ', $transition['to']) . '".');
    }

    /**
     * CHANGED (A1): auto-detect format is NULL at the DB layer — never store the
     * 'auto' sentinel (the enum column rejects it and NULL is the honest value).
     */
    protected function normalizeAssessmentFormat(?string $format): ?string
    {
        return in_array($format, ['primary', 'o-level', 'a-level'], true) ? $format : null;
    }

    protected function syncReportComponents(Exam $exam, array $components): void
    {
        if (! ($exam->is_report_card ?? false)) {
            $exam->reportComponents()->detach();
            return;
        }

        $syncPayload = [];
        foreach (array_values($components) as $index => $component) {
            $componentExamId = isset($component['exam_id']) ? (int) $component['exam_id'] : 0;
            $weight = isset($component['weight']) ? (float) $component['weight'] : 0;

            if ($componentExamId <= 0 || $componentExamId === (int) $exam->id) {
                continue;
            }

            $syncPayload[$componentExamId] = [
                'weight' => $weight,
                'display_order' => $index,
            ];
        }

        $exam->reportComponents()->sync($syncPayload);
    }

    public function publish(Exam $exam)
    {
        // If already published, return early
        if ($exam->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Exam is already published'
            ], 400);
        }

        // CHANGED (A2): publishing requires the exam to be LOCKED first — you cannot
        // email results to guardians while marks entry is still open.
        if ($exam->status !== Exam::STATUS_LOCKED) {
            return response()->json([
                'success' => false,
                'message' => 'Lock marks entry before publishing. Current status: ' . str_replace('_', ' ', $exam->status) . '.',
            ], 422);
        }

        // Mark exam as published
        // CHANGED (A2): keep the legacy is_published flag in sync with the status workflow.
        $exam->update(['is_published' => true, 'status' => Exam::STATUS_PUBLISHED]);

        // Notify guardians via queued mail
        $studentIds = $exam->grades()->distinct()->pluck('student_id');
        $students = \App\Models\Student::with('guardians')->whereIn('id', $studentIds)->get();
        foreach ($students as $student) {
            $guardian = $student->primaryGuardian();
            $email = $guardian?->email ?? $student->email;
            if ($email) {
                Mail::to($email)->queue(new ExamResultsPublishedMail($student, $exam));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Exam published and guardians notified'
        ]);
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return redirect()->route('exams.index')->with('success', 'Exam deleted successfully.');
    }

    /**
     * Add a schedule entry to an exam.
     */
    public function addSchedule(StoreExamScheduleRequest $request, Exam $exam)
    {
        $validated = $request->validated();

        $validated['exam_id'] = $exam->id;
        ExamSchedule::create($validated);

        return redirect()->route('exams.show', $exam)->with('success', 'Schedule added.');
    }

    /**
     * Remove a schedule entry.
     */
    public function removeSchedule(ExamSchedule $schedule)
    {
        $examId = $schedule->exam_id;
        $schedule->delete();
        return redirect()->route('exams.show', $examId)->with('success', 'Schedule removed.');
    }
}
