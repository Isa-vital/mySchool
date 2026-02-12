<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;

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
        return view('exams.create', compact('academicYears', 'classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:school_classes,id',
        ]);

        $exam = Exam::create($validated);

        // Auto-create exam schedules for selected classes and their subjects
        if ($request->filled('class_ids')) {
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
        $exam->load(['academicYear', 'term', 'schedules.schoolClass', 'schedules.subject', 'grades']);
        $classes = SchoolClass::active()->orderBy('level')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        return view('exams.show', compact('exam', 'classes', 'subjects'));
    }

    public function edit(Exam $exam)
    {
        $academicYears = AcademicYear::with('terms')->orderBy('start_date', 'desc')->get();
        return view('exams.edit', compact('exam', 'academicYears'));
    }

    public function update(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);

        // Handle unchecked checkbox
        $validated['is_published'] = $request->has('is_published');

        $exam->update($validated);
        return redirect()->route('exams.index')->with('success', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return redirect()->route('exams.index')->with('success', 'Exam deleted successfully.');
    }

    /**
     * Add a schedule entry to an exam.
     */
    public function addSchedule(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'full_marks' => 'required|numeric|min:1',
            'pass_marks' => 'required|numeric|min:0',
            'room' => 'nullable|string|max:50',
        ]);

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
