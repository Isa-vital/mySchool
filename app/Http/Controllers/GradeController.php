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
        $exams = Exam::with(['academicYear', 'term'])
            ->where('is_report_card', false)
            ->orderBy('created_at', 'desc')
            ->get();

        $reportExams = Exam::with(['academicYear', 'term', 'reportComponents'])
            ->where('is_report_card', true)
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

        if ($selectedClassId) {
            $class    = SchoolClass::find($selectedClassId);
            $subjects = $class ? $class->subjects : collect();

            $enrollments = \App\Models\Enrollment::with('student')
                ->where('school_class_id', $selectedClassId)
                ->where('academic_year_id', $reportExam->academic_year_id)
                ->where('status', 'active')
                ->get();
            $students = $enrollments->pluck('student')->filter()->values();

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
            'reportExam', 'componentExams', 'classes', 'students',
            'subject', 'subjects', 'selectedClassId', 'selectedSubjectId',
            'gridGrades', 'fullMarks', 'weightTotal'
        ));
    }

    /**
     * Save grid grades — persists marks for each component exam individually.
     */
    public function saveGrid(Request $request, Exam $reportExam)
    {
        abort_unless($reportExam->is_report_card, 422, 'Only composite report-card exams support grid entry.');

        $request->validate([
            'class_id'   => 'required|integer',
            'subject_id' => 'required|integer',
            'grades'     => 'array',
        ]);

        $componentExams = $reportExam->reportComponents()->get()->keyBy('id');

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
                $resolved = AssessmentGradingService::resolveForExam($reportExam, $marks);

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
        $gradingRanges = collect(AssessmentGradingService::previewRangesForExam($exam));
        $fullMarks = 100;
        $passMarks = 40;

        if ($selectedClassId) {
            $class = SchoolClass::find($selectedClassId);
            $subjects = $class ? $class->subjects : collect();

            $enrollments = Enrollment::with('student')
                ->where('school_class_id', $selectedClassId)
                ->where('academic_year_id', $exam->academic_year_id)
                ->where('status', 'active')
                ->get();
            $students = $enrollments->pluck('student');

            if ($selectedSubjectId) {
                $subject = Subject::find($selectedSubjectId);
                $existingGrades = Grade::where('exam_id', $exam->id)
                    ->where('school_class_id', $selectedClassId)
                    ->where('subject_id', $selectedSubjectId)
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

        return view('grades.enter', compact('exam', 'classes', 'students', 'existingGrades', 'subjects', 'subject', 'selectedClassId', 'selectedSubjectId', 'gradingRanges', 'fullMarks', 'passMarks'));
    }

    public function save(SaveGradesRequest $request, Exam $exam)
    {
        abort_if($exam->is_report_card, 422, 'Report-card exams are computed from component exams and do not accept direct grade entry.');

        $validated = $request->validated();

        foreach ($request->grades as $gradeData) {
            if (isset($gradeData['marks_obtained']) && $gradeData['marks_obtained'] !== null && $gradeData['marks_obtained'] !== '') {
                $marks = (float) $gradeData['marks_obtained'];
                $resolved = AssessmentGradingService::resolveForExam($exam, $marks);
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
