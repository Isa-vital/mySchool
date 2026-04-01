<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\Subject;
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
        return view('exams.create', compact('academicYears', 'classes'));
    }

    public function store(StoreExamRequest $request)
    {
        $validated = $request->validated();

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

    public function update(UpdateExamRequest $request, Exam $exam)
    {
        $validated = $request->validated();

        // Handle unchecked checkbox
        $validated['is_published'] = $request->has('is_published');

        $exam->update($validated);

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
