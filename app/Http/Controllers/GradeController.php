<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\AcademicYear;
use App\Http\Requests\SaveGradesRequest;
use App\Services\AssessmentGradingService;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        // CHANGED: include all non-report-card exams (component exams) in the regular entry list,
        // and separately provide report-card exams for the grid entry path.
        // CHANGED (UX): default both lists to the current academic year so stale exams
        // don't clutter the pickers (?all=1 shows everything).
        $currentYear = AcademicYear::current();
        $scopeToYear = $currentYear && ! $request->boolean('all');

        $exams = Exam::with(['academicYear', 'term'])
            ->where('is_report_card', false)
            ->when($scopeToYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->orderBy('created_at', 'desc')
            ->get();

        $reportExams = Exam::with(['academicYear', 'term', 'reportComponents'])
            ->where('is_report_card', true)
            ->when($scopeToYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->orderBy('created_at', 'desc')
            ->get();

        $classes = SchoolClass::active()->orderBy('level')->get();
        $subjects = Subject::active()->orderBy('name')->get();

        // Redirect: grid entry for report-card exams, single entry for regular exams
        if ($request->filled('report_exam_id') && $request->filled('class_id') && $request->filled('subject_id')) {
            return redirect()->route('grades.enter-grid', [
                'report_exam' => $request->report_exam_id,
                'class_id'    => $request->class_id,
                'subject_id'  => $request->subject_id,
            ]);
        }

        if ($request->filled('exam_id') && $request->filled('class_id') && $request->filled('subject_id')) {
            return redirect()->route('grades.enter', [
                'exam'       => $request->exam_id,
                'class_id'   => $request->class_id,
                'subject_id' => $request->subject_id,
            ]);
        }

        return view('grades.index', compact('exams', 'reportExams', 'classes', 'subjects'));
    }

    /**
     * Grid entry: enter marks for ALL component exams in one view
     * (like university coursework + final exam side by side).
     */
    public function enterGrid(Request $request, Exam $reportExam)
    {
        abort_unless($reportExam->is_report_card, 422, 'Only composite report-card exams support grid entry.');

        $componentExams = $reportExam->reportComponents()->get();
        $classes        = SchoolClass::active()->with('subjects')->orderBy('level')->get();
        $selectedClassId   = $request->get('class_id');
        $selectedSubjectId = $request->get('subject_id');

        $students      = collect();
        $subject       = null;
        $subjects      = collect();
        $gridGrades    = [];   // [student_id][exam_id] => marks
        $fullMarks     = [];   // [exam_id] => full_marks
        $schedules     = collect();
        $noCombinationCount = 0;

        if ($selectedClassId) {
            $class    = SchoolClass::find($selectedClassId);
            $subjects = $class ? $class->subjects : collect();

            // CHANGED (A6): the multi-exam grid writes ONE score per subject — subjects
            // with weighted components must be entered per exam instead.
            if ($selectedSubjectId) {
                $gridSubject = Subject::find($selectedSubjectId);
                if ($gridSubject && $gridSubject->hasComponents()) {
                    return redirect()->route('grades.enter-grid', ['report_exam' => $reportExam->id, 'class_id' => $selectedClassId])
                        ->with('error', "\"{$gridSubject->name}\" is assessed in weighted components (" . $gridSubject->components()->pluck('name')->implode(', ') . '). Enter its marks per exam from the Grades page instead.');
                }
            }

            $enrollments = \App\Models\Enrollment::with('student')
                ->where('school_class_id', $selectedClassId)
                ->where('academic_year_id', $reportExam->academic_year_id)
                ->where('status', 'active')
                ->takingSubject($class, $selectedSubjectId)
                ->get();
            $students = $enrollments->pluck('student')->filter()->values();
            $noCombinationCount = $class && $class->category() === 'a_level'
                ? $enrollments->whereNull('subject_combination_id')->count()
                : 0;

            if ($selectedSubjectId) {
                $subject = Subject::find($selectedSubjectId);

                foreach ($componentExams as $compExam) {
                    $schedule = \App\Models\ExamSchedule::where('exam_id', $compExam->id)
                        ->where('school_class_id', $selectedClassId)
                        ->where('subject_id', $selectedSubjectId)
                        ->first();
                    $fullMarks[$compExam->id] = $schedule ? (float)$schedule->full_marks : (float)($compExam->max_points ?? 100);

                    $examGrades = Grade::where('exam_id', $compExam->id)
                        ->where('school_class_id', $selectedClassId)
                        ->where('subject_id', $selectedSubjectId)
                        ->get()
                        ->keyBy('student_id');

                    foreach ($examGrades as $studentId => $grade) {
                        $gridGrades[$studentId][$compExam->id] = $grade->marks_obtained;
                    }
                }
            }
        }

        $weightTotal = (float) $componentExams->sum(fn($e) => (float)($e->pivot->weight ?? 0));
        if ($weightTotal <= 0) {
            $weightTotal = max($componentExams->count(), 1);
        }

        return view('grades.enter-grid', compact(
            'reportExam',
            'componentExams',
            'classes',
            'students',
            'subject',
            'subjects',
            'selectedClassId',
            'selectedSubjectId',
            'gridGrades',
            'fullMarks',
            'weightTotal',
            'noCombinationCount'
        ));
    }

    /**
     * Save grid grades — persists marks for each component exam individually.
     */
    public function saveGrid(Request $request, Exam $reportExam)
    {
        abort_unless($reportExam->is_report_card, 422, 'Only composite report-card exams support grid entry.');

        // CHANGED (A2): grid writes land on component exams — refuse when the report exam
        // or any targeted component exam is locked/published.
        abort_unless($reportExam->acceptsMarks(), 423, 'This report card is locked. Ask a moderator to unlock it before editing marks.');

        $request->validate([
            'class_id'   => 'required|integer',
            'subject_id' => 'required|integer',
            'grades'     => 'array',
        ]);

        $componentExams = $reportExam->reportComponents()->get()->keyBy('id');

        // CHANGED (A2): locked component exams reject writes too.
        $lockedComponent = $componentExams->first(fn($e) => ! $e->acceptsMarks());
        abort_if((bool) $lockedComponent, 423, 'Exam "' . ($lockedComponent->name ?? '') . '" is locked. Ask a moderator to unlock it before editing marks.');

        // CHANGED: resolve grades against the selected class so 'auto' format
        // uses the class-appropriate scale (primary/o-level/a-level).
        $gradeClass = SchoolClass::find((int) $request->class_id);

        foreach ($request->grades as $studentId => $examMarks) {
            foreach ($examMarks as $examId => $marks) {
                if ($marks === null || $marks === '') {
                    continue;
                }

                $compExam = $componentExams->get((int) $examId);
                if (! $compExam) {
                    continue;
                }

                $marks    = (float) $marks;
                // CHANGED: was resolveForExam($reportExam, $marks)
                $resolved = AssessmentGradingService::resolveForExam($reportExam, $marks, $gradeClass);

                Grade::updateOrCreate(
                    [
                        'exam_id'    => (int) $examId,
                        'student_id' => (int) $studentId,
                        'subject_id' => (int) $request->subject_id,
                    ],
                    [
                        'school_class_id'  => (int) $request->class_id,
                        'marks_obtained'   => $marks,
                        'grade_letter'     => $resolved['grade'],
                        'achievement_level' => $resolved['description'],
                        'graded_by'        => auth()->id(),
                    ]
                );
            }
        }

        return redirect()->route('grades.enter-grid', [
            'report_exam' => $reportExam->id,
            'class_id'    => $request->class_id,
            'subject_id'  => $request->subject_id,
        ])->with('success', 'Grades saved successfully.');
    }

    public function enter(Request $request, Exam $exam)
    {
        abort_if($exam->is_report_card, 422, 'Report-card exams are computed from component exams and do not accept direct grade entry.');

        $classes = SchoolClass::active()->with('subjects')->orderBy('level')->get();
        $selectedClassId = $request->get('class_id');
        $selectedSubjectId = $request->get('subject_id');

        $students = collect();
        $existingGrades = collect();
        $subjects = collect();
        $subject = null;

        // CHANGED: provide grading ranges + this subject's marks so the entry
        // form can show a live grade preview and validate against full marks.
        // CHANGED: ranges are computed after the class is known (see below) so
        // 'auto' format previews the class-appropriate scale.
        $gradingRanges = collect();
        $fullMarks = 100;
        $passMarks = 40;
        $class = null;
        $noCombinationCount = 0;

        if ($selectedClassId) {
            $class = SchoolClass::find($selectedClassId);
            $subjects = $class ? $class->subjects : collect();

            $enrollments = Enrollment::with('student')
                ->where('school_class_id', $selectedClassId)
                ->where('academic_year_id', $exam->academic_year_id)
                ->where('status', 'active')
                ->takingSubject($class, $selectedSubjectId)
                ->get();
            $students = $enrollments->pluck('student');
            $noCombinationCount = $class && $class->category() === 'a_level'
                ? $enrollments->whereNull('subject_combination_id')->count()
                : 0;

            if ($selectedSubjectId) {
                $subject = Subject::find($selectedSubjectId);
                $existingGrades = Grade::where('exam_id', $exam->id)
                    ->where('school_class_id', $selectedClassId)
                    ->where('subject_id', $selectedSubjectId)
                    // CHANGED (A6): whole-subject rows only; component rows load separately below.
                    ->whereNull('subject_component_id')
                    ->get()
                    ->keyBy('student_id');

                // Pull this exam's marks for the class/subject if a schedule exists.
                $schedule = \App\Models\ExamSchedule::where('exam_id', $exam->id)
                    ->where('school_class_id', $selectedClassId)
                    ->where('subject_id', $selectedSubjectId)
                    ->first();
                if ($schedule) {
                    $fullMarks = (float) $schedule->full_marks ?: 100;
                    $passMarks = (float) $schedule->pass_marks ?: 40;
                }
            }
        }

        // CHANGED: class-aware ranges so 'auto' format previews correctly.
        $gradingRanges = collect(AssessmentGradingService::previewRangesForExam($exam, $class));

        // CHANGED (A6): subjects with weighted components (Paper 1/2 …) use a
        // component-columns entry table instead of the single-score form.
        $subjectComponents = $subject ? $subject->components()->get() : collect();
        $existingComponentMarks = ($subject && $subjectComponents->isNotEmpty() && $selectedClassId)
            ? \App\Services\ComponentMarksService::existingMarks($exam, (int) $selectedClassId, $subject)
            : collect();

        // CHANGED (UX): per-subject progress for this class — powers the subject pill bar
        // and "Next subject" button so users don't re-select filters for every subject.
        $subjectProgress = collect();
        $nextSubject = null;
        if ($selectedClassId && $subjects->count()) {
            $enteredBySubject = Grade::where('exam_id', $exam->id)
                ->where('school_class_id', $selectedClassId)
                ->selectRaw('subject_id, count(*) as entered')
                ->groupBy('subject_id')
                ->pluck('entered', 'subject_id');

            $enrolledCount = $students->count();
            $subjectProgress = $subjects->map(fn($s) => [
                'subject' => $s,
                'entered' => (int) ($enteredBySubject[$s->id] ?? 0),
                'complete' => $enrolledCount > 0 && (int) ($enteredBySubject[$s->id] ?? 0) >= $enrolledCount,
                'current' => (int) $s->id === (int) $selectedSubjectId,
            ])->values();

            // Next incomplete subject after the current one (wrapping around).
            $ordered = $subjectProgress;
            if ($selectedSubjectId) {
                $currentIndex = $ordered->search(fn($p) => $p['current']);
                if ($currentIndex !== false) {
                    $ordered = $ordered->slice($currentIndex + 1)->concat($ordered->take($currentIndex));
                }
            }
            $nextSubject = $ordered->first(fn($p) => ! $p['complete'] && ! $p['current'])['subject'] ?? null;
        }

        return view('grades.enter', compact('exam', 'classes', 'students', 'existingGrades', 'subjects', 'subject', 'selectedClassId', 'selectedSubjectId', 'gradingRanges', 'fullMarks', 'passMarks', 'subjectProgress', 'nextSubject', 'subjectComponents', 'existingComponentMarks', 'noCombinationCount'));
    }

    public function save(SaveGradesRequest $request, Exam $exam)
    {
        abort_if($exam->is_report_card, 422, 'Report-card exams are computed from component exams and do not accept direct grade entry.');

        // CHANGED (A2): marks are frozen once the exam is locked/published. A moderator
        // must explicitly unlock the exam before any further edits.
        abort_unless($exam->acceptsMarks(), 423, 'Marks for this exam are locked. Ask a moderator to unlock it before editing.');

        $validated = $request->validated();

        // CHANGED (A6): component-subject path — raw scores per weighted component.
        $componentSubject = Subject::find($request->subject_id);
        if ($componentSubject && $componentSubject->hasComponents() && $request->filled('component_marks')) {
            $saved = \App\Services\ComponentMarksService::saveMarks(
                $exam,
                (int) $request->class_id,
                $componentSubject,
                (array) $request->input('component_marks', []),
                (int) auth()->id()
            );

            return redirect()->route('grades.enter', [
                'exam' => $exam->id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
            ])->with('success', "{$saved} component score(s) saved.");
        }

        // CHANGED: resolve against the selected class so 'auto' format uses the
        // class-appropriate scale (primary/o-level/a-level).
        $gradeClass = SchoolClass::find((int) $request->class_id);

        foreach ($request->grades as $gradeData) {
            if (isset($gradeData['marks_obtained']) && $gradeData['marks_obtained'] !== null && $gradeData['marks_obtained'] !== '') {
                $marks = (float) $gradeData['marks_obtained'];
                // CHANGED: was resolveForExam($exam, $marks)
                $resolved = AssessmentGradingService::resolveForExam($exam, $marks, $gradeClass);
                $gradeLetter = $resolved['grade'];
                $achievementLevel = $resolved['description'];

                Grade::updateOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'student_id' => $gradeData['student_id'],
                        'subject_id' => $request->subject_id,
                    ],
                    [
                        'school_class_id' => $request->class_id,
                        'marks_obtained' => $marks,
                        'grade_letter' => $gradeLetter,
                        'achievement_level' => $achievementLevel,
                        'remarks' => $gradeData['remarks'] ?? null,
                        'graded_by' => auth()->id(),
                    ]
                );
            }
        }

        return redirect()->route('grades.enter', [
            'exam' => $exam->id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
        ])->with('success', 'Grades saved successfully.');
    }
}
