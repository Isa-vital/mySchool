<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Staff;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $currentYear = AcademicYear::current();

        $stats = [
            'total_students' => Student::active()->count(),
            'total_staff' => Staff::active()->count(),
            'total_enrolled' => $currentYear ? Enrollment::where('academic_year_id', $currentYear->id)->where('status', 'active')->count() : 0,
            'attendance_today' => Attendance::whereDate('date', today())->where('status', 'present')->count(),
            'absent_today' => Attendance::whereDate('date', today())->where('status', 'absent')->count(),
            'total_fees_collected' => $currentYear ? Payment::whereHas('invoice', fn($q) => $q->where('academic_year_id', $currentYear->id))->sum('amount') : 0,
            'total_fees_pending' => $currentYear ? Invoice::where('academic_year_id', $currentYear->id)->sum('balance') : 0,
            'recent_payments' => Payment::with('student')->latest()->take(5)->get(),
            'recent_students' => Student::latest()->take(5)->get(),
        ];

        return view('dashboard', compact('stats'));
    }
}
