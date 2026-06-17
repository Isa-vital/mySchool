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
        $exams = Exam::with(['academicYear', 'term'])
            ->where('is_report_card', false)
            ->orderBy('created_at', 'desc')
            ->get();
        $classes = SchoolClass::active()->orderBy('level')->get();
        $subjects = Subject::active()->orderBy('name')->get();

        // If exam_id is provided, redirect to the enter page
        if ($request->filled('exam_id') && $request->filled('class_id') && $request->filled('subject_id')) {
            return redirect()->route('grades.enter', [
                'exam' => $request->exam_id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
            ]);
        }

        return view('grades.index', compact('exams', 'classes', 'subjects'));
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
