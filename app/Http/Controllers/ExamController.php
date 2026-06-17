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
    public function index()
    {
        $exams = Exam::with(['academicYear', 'term'])->orderBy('created_at', 'desc')->paginate(20);
        return view('exams.index', compact('exams'));
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

        return view('exams.create', compact('academicYears', 'classes', 'gradingScales', 'availableComponentExams'));
    }

    public function store(StoreExamRequest $request)
    {
        $validated = $request->validated();

        $validated['assessment_format'] = $validated['assessment_format'] ?? setting('report_card_format', 'primary');
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
        return view('exams.show', compact('exam', 'classes', 'subjects'));
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

        return view('exams.edit', compact('exam', 'academicYears', 'gradingScales', 'availableComponentExams'));
    }

    public function update(UpdateExamRequest $request, Exam $exam)
    {
        $validated = $request->validated();

        $validated['assessment_format'] = $validated['assessment_format'] ?? $exam->assessment_format ?? setting('report_card_format', 'primary');
        $validated['max_points'] = $validated['max_points'] ?? $exam->max_points ?? 100;
        $validated['is_report_card'] = $request->boolean('is_report_card');

        // Handle unchecked checkbox
        $validated['is_published'] = $request->has('is_published');

        $exam->update($validated);
        $this->syncReportComponents($exam, $validated['report_components'] ?? []);

        // If exam is being published, notify guardians
        if ($request->has('is_published') && $exam->is_published) {
            $studentIds = $exam->grades()->distinct()->pluck('student_id');
            $students = \App\Models\Student::with('guardians')->whereIn('id', $studentIds)->get();
            foreach ($students as $student) {
                $guardian = $student->primaryGuardian();
                $email = $guardian?->email ?? $student->email;
                if ($email) {
                    Mail::to($email)->queue(new ExamResultsPublishedMail($student, $exam));
                }
            }
        }

        return redirect()->route('exams.index')->with('success', 'Exam updated successfully.');
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

        // Mark exam as published
        $exam->update(['is_published' => true]);

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
