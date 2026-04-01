<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Guardian;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Exam;
use Illuminate\Http\Request;

class ParentPortalController extends Controller
{
    protected function getGuardianStudents()
    {
        $user = auth()->user();
        $guardian = Guardian::where('email', $user->email)->first();

        if (!$guardian) {
            return collect();
        }

        return $guardian->students()->with([
            'enrollments' => function ($q) {
                $currentYear = AcademicYear::current();
                if ($currentYear) {
                    $q->where('academic_year_id', $currentYear->id)->with(['schoolClass', 'section']);
                }
            },
        ])->get();
    }

    public function dashboard()
    {
        $students = $this->getGuardianStudents();
        $currentYear = AcademicYear::current();

        $data = $students->map(function ($student) use ($currentYear) {
            $enrollment = $student->enrollments->first();
            $todayAttendance = Attendance::where('student_id', $student->id)
                ->whereDate('date', today())
                ->first();

            $unpaidInvoices = $student->invoices()
                ->whereIn('status', ['unpaid', 'partial'])
                ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
                ->get();

            return [
                'student' => $student,
                'enrollment' => $enrollment,
                'today_attendance' => $todayAttendance,
                'unpaid_invoices' => $unpaidInvoices,
                'total_balance' => $unpaidInvoices->sum('balance'),
            ];
        });

        return view('parent-portal.dashboard', compact('data'));
    }

    public function childDetails(Student $student)
    {
        $students = $this->getGuardianStudents();
        abort_unless($students->contains('id', $student->id), 403);

        $currentYear = AcademicYear::current();
        $student->load([
            'enrollments.schoolClass',
            'enrollments.section',
            'enrollments.academicYear',
            'invoices' => fn($q) => $q->when($currentYear, fn($q2) => $q2->where('academic_year_id', $currentYear->id)),
            'invoices.payments',
            'invoices.term',
        ]);

        return view('parent-portal.child-details', compact('student'));
    }

    public function attendance(Request $request, Student $student)
    {
        $students = $this->getGuardianStudents();
        abort_unless($students->contains('id', $student->id), 403);

        $month = $request->get('month', now()->format('Y-m'));
        $startDate = \Carbon\Carbon::parse($month)->startOfMonth();
        $endDate = \Carbon\Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        $summary = [
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
        ];

        return view('parent-portal.attendance', compact('student', 'attendances', 'summary', 'month'));
    }

    public function results(Student $student)
    {
        $students = $this->getGuardianStudents();
        abort_unless($students->contains('id', $student->id), 403);

        $exams = Exam::where('is_published', true)
            ->with(['academicYear', 'term'])
            ->whereHas('grades', fn($q) => $q->where('student_id', $student->id))
            ->orderBy('created_at', 'desc')
            ->get();

        $results = $exams->map(function ($exam) use ($student) {
            $grades = Grade::with('subject')
                ->where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->get();
            return [
                'exam' => $exam,
                'grades' => $grades,
                'average' => $grades->avg('marks_obtained'),
            ];
        });

        return view('parent-portal.results', compact('student', 'results'));
    }

    public function fees(Student $student)
    {
        $students = $this->getGuardianStudents();
        abort_unless($students->contains('id', $student->id), 403);

        $invoices = $student->invoices()
            ->with(['academicYear', 'term', 'items.feeType', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('parent-portal.fees', compact('student', 'invoices'));
    }
}
