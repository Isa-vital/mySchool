<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('class_id')) {
            $currentYear = AcademicYear::current();
            if ($currentYear) {
                $query->whereHas('enrollments', function ($q) use ($request, $currentYear) {
                    $q->where('school_class_id', $request->class_id)
                        ->where('academic_year_id', $currentYear->id);
                });
            }
        }

        $students = $query->with(['enrollments' => function ($q) use ($classes) {
            $currentYear = AcademicYear::current();
            if ($currentYear) {
                $q->where('academic_year_id', $currentYear->id)->with(['schoolClass', 'section']);
            }
        }])->orderBy('first_name')->paginate(20)->withQueryString();
        $classes = SchoolClass::active()->orderBy('level')->get();

        return view('students.index', compact('students', 'classes'));
    }

    public function create()
    {
        $classes = SchoolClass::active()->with('sections')->orderBy('level')->get();
        $academicYear = AcademicYear::current();
        return view('students.create', compact('classes', 'academicYear'));
    }

    public function store(StoreStudentRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('students', 'public');
        }

        $student = Student::create($validated);

        // Create guardian if provided
        if ($request->filled('guardian_first_name')) {
            $guardian = Guardian::create([
                'first_name' => $request->guardian_first_name,
                'last_name' => $request->guardian_last_name,
                'relationship' => $request->guardian_relationship,
                'phone' => $request->guardian_phone,
                'email' => $request->guardian_email,
                'address' => $request->guardian_address,
                'occupation' => $request->guardian_occupation,
            ]);
            $student->guardians()->attach($guardian->id, ['is_primary' => true]);
        }

        // Enroll in class if provided
        if ($request->filled('class_id')) {
            $currentYear = AcademicYear::current();
            if ($currentYear) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'school_class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'academic_year_id' => $currentYear->id,
                    'status' => 'active',
                ]);
            }
        }

        return redirect()->route('students.index')->with('success', 'Student registered successfully.');
    }

    public function show(Student $student)
    {
        $student->load([
            'guardians',
            'enrollments.schoolClass',
            'enrollments.section',
            'enrollments.academicYear',
            'invoices.term',
            'invoices.payments',
            'payments.invoice',
        ]);

        // Get book issues for this student
        $bookIssues = \App\Models\BookIssue::with('book')
            ->where('borrower_type', 'App\\Models\\Student')
            ->where('borrower_id', $student->id)
            ->latest()
            ->get();

        return view('students.show', compact('student', 'bookIssues'));
    }

    public function edit(Student $student)
    {
        $classes = SchoolClass::active()->with('sections')->orderBy('level')->get();
        $student->load('guardians');
        $currentEnrollment = $student->currentEnrollment();
        return view('students.edit', compact('student', 'classes', 'currentEnrollment'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('students', 'public');
        }

        $student->update($validated);

        return redirect()->route('students.show', $student)->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }
}
