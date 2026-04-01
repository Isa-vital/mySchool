<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Attendance;
use App\Models\TimetableSlot;
use App\Models\Grade;
use App\Models\Exam;
use Illuminate\Http\Request;

class TeacherPortalController extends Controller
{
    protected function getStaff()
    {
        return Staff::where('user_id', auth()->id())->firstOrFail();
    }

    public function dashboard()
    {
        $staff = $this->getStaff();
        $currentYear = AcademicYear::current();

        $todaySlots = collect();
        if ($currentYear) {
            $todaySlots = TimetableSlot::with(['subject', 'schoolClass', 'section'])
                ->where('staff_id', $staff->id)
                ->where('academic_year_id', $currentYear->id)
                ->where('day_of_week', now()->dayOfWeekIso)
                ->orderBy('start_time')
                ->get();
        }

        $myClasses = collect();
        if ($currentYear) {
            $myClasses = TimetableSlot::where('staff_id', $staff->id)
                ->where('academic_year_id', $currentYear->id)
                ->with('schoolClass')
                ->get()
                ->pluck('schoolClass')
                ->unique('id')
                ->values();
        }

        return view('teacher-portal.dashboard', compact('staff', 'todaySlots', 'myClasses'));
    }

    public function timetable()
    {
        $staff = $this->getStaff();
        $currentYear = AcademicYear::current();

        $slots = collect();
        if ($currentYear) {
            $slots = TimetableSlot::with(['subject', 'schoolClass', 'section'])
                ->where('staff_id', $staff->id)
                ->where('academic_year_id', $currentYear->id)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week');
        }

        return view('teacher-portal.timetable', compact('slots'));
    }

    public function attendance(Request $request)
    {
        $staff = $this->getStaff();
        $currentYear = AcademicYear::current();

        $myClassIds = TimetableSlot::where('staff_id', $staff->id)
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->distinct()
            ->pluck('school_class_id');

        $classes = SchoolClass::whereIn('id', $myClassIds)->with('sections')->orderBy('level')->get();
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

        return view('teacher-portal.attendance', compact('classes', 'students', 'attendances', 'selectedClassId', 'selectedSectionId', 'date'));
    }

    public function storeAttendance(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'date' => 'required|date|before_or_equal:today',
            'attendance' => 'required|array|min:1',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent,late,excused',
            'attendance.*.remarks' => 'nullable|string|max:500',
        ]);

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

        return redirect()->route('teacher.attendance', [
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'date' => $request->date,
        ])->with('success', 'Attendance saved successfully.');
    }

    public function grades(Request $request)
    {
        $staff = $this->getStaff();
        $currentYear = AcademicYear::current();
        $exams = Exam::with(['academicYear', 'term'])->orderBy('created_at', 'desc')->get();

        $myClassIds = TimetableSlot::where('staff_id', $staff->id)
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->distinct()
            ->pluck('school_class_id');

        $classes = SchoolClass::whereIn('id', $myClassIds)->with('subjects')->orderBy('level')->get();
        $subjects = Subject::active()->orderBy('name')->get();

        return view('teacher-portal.grades', compact('exams', 'classes', 'subjects'));
    }

    public function enterGrades(Request $request, Exam $exam)
    {
        $staff = $this->getStaff();
        $currentYear = AcademicYear::current();

        $myClassIds = TimetableSlot::where('staff_id', $staff->id)
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->distinct()
            ->pluck('school_class_id');

        $classes = SchoolClass::whereIn('id', $myClassIds)->with('subjects')->orderBy('level')->get();
        $selectedClassId = $request->get('class_id');
        $selectedSubjectId = $request->get('subject_id');

        $students = collect();
        $existingGrades = collect();
        $subjects = collect();
        $subject = null;

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
            }
        }

        return view('teacher-portal.enter-grades', compact('exam', 'classes', 'students', 'existingGrades', 'subjects', 'subject', 'selectedClassId', 'selectedSubjectId'));
    }

    public function saveGrades(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'grades' => 'required|array|min:1',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.marks_obtained' => 'nullable|numeric|min:0|max:100',
            'grades.*.remarks' => 'nullable|string|max:500',
        ]);

        $gradingScale = \App\Models\GradingScale::where('is_default', true)->first();
        $ranges = $gradingScale ? $gradingScale->ranges()->orderBy('min_mark', 'desc')->get() : collect();

        foreach ($request->grades as $gradeData) {
            if (isset($gradeData['marks_obtained']) && $gradeData['marks_obtained'] !== null && $gradeData['marks_obtained'] !== '') {
                $marks = (float) $gradeData['marks_obtained'];
                $gradeLetter = null;
                foreach ($ranges as $range) {
                    if ($marks >= $range->min_mark && $marks <= $range->max_mark) {
                        $gradeLetter = $range->grade;
                        break;
                    }
                }

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
                        'remarks' => $gradeData['remarks'] ?? null,
                        'graded_by' => auth()->id(),
                    ]
                );
            }
        }

        return redirect()->route('teacher.enter-grades', [
            'exam' => $exam->id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
        ])->with('success', 'Grades saved successfully.');
    }
}
