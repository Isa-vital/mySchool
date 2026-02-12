<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\SchoolClass;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::where('is_published', true)->with(['academicYear', 'term'])->orderBy('created_at', 'desc')->get();
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
        $grades = Grade::with('subject')
            ->where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->get();

        $enrollment = $student->enrollments()->where('academic_year_id', $exam->academic_year_id)->first();

        return view('report-cards.show', compact('student', 'exam', 'grades', 'enrollment'));
    }

    public function pdf(Student $student, Exam $exam)
    {
        $grades = Grade::with('subject')
            ->where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->get();

        $enrollment = $student->enrollments()->where('academic_year_id', $exam->academic_year_id)->first();

        $pdf = Pdf::loadView('report-cards.pdf', compact('student', 'exam', 'grades', 'enrollment'));
        return $pdf->download("report-card-{$student->admission_number}-{$exam->name}.pdf");
    }
}
