<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Http\Requests\StoreAttendanceRequest;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::active()->with('sections')->orderBy('level')->get();
        $currentYear = AcademicYear::current();
        $selectedClassId = $request->get('class_id');
        $selectedSectionId = $request->get('section_id');
        $date = $request->get('date', today()->format('Y-m-d'));

        $students = collect();
        $attendances = collect();

        if ($selectedClassId && $currentYear) {
            $enrollmentQuery = Enrollment::with('student')
                ->where('school_class_id', $selectedClassId)
                ->where('academic_year_id', $currentYear->id)
                ->where('status', 'active');

            if ($selectedSectionId) {
                $enrollmentQuery->where('section_id', $selectedSectionId);
            }

            $students = $enrollmentQuery->get()->pluck('student');

            $attendances = Attendance::where('school_class_id', $selectedClassId)
                ->where('academic_year_id', $currentYear->id)
                ->where('date', $date)
                ->get()
                ->keyBy('student_id');
        }

        return view('attendance.index', compact('classes', 'students', 'attendances', 'selectedClassId', 'selectedSectionId', 'date'));
    }

    public function store(StoreAttendanceRequest $request)
    {
        $validated = $request->validated();

        $currentYear = AcademicYear::current();

        foreach ($request->attendance as $record) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'date' => $request->date,
                ],
                [
                    'school_class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'academic_year_id' => $currentYear->id,
                    'status' => $record['status'],
                    'remarks' => $record['remarks'] ?? null,
                    'marked_by' => auth()->id(),
                ]
            );
        }

        return redirect()->route('attendance.index', [
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'date' => $request->date,
        ])->with('success', 'Attendance saved successfully.');
    }

    public function report(Request $request)
    {
        $classes = SchoolClass::active()->orderBy('level')->get();
        $currentYear = AcademicYear::current();
        $report = collect();

        if ($request->filled('class_id') && $request->filled('from_date') && $request->filled('to_date')) {
            $report = Attendance::with('student')
                ->where('school_class_id', $request->class_id)
                ->whereBetween('date', [$request->from_date, $request->to_date])
                ->get()
                ->groupBy('student_id')
                ->map(function ($records) {
                    $student = $records->first()->student;
                    return [
                        'student' => $student,
                        'total' => $records->count(),
                        'present' => $records->where('status', 'present')->count(),
                        'absent' => $records->where('status', 'absent')->count(),
                        'late' => $records->where('status', 'late')->count(),
                        'excused' => $records->where('status', 'excused')->count(),
                    ];
                });
        }

        return view('attendance.report', compact('classes', 'report'));
    }
}
