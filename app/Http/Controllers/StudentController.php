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
use Illuminate\Support\Facades\Storage;

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

        $students = $query->with(['enrollments' => function ($q) {
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
        // CHANGED: suggest the next sequential admission number (e.g. ADM00152 -> ADM00153)
        $nextAdmissionNumber = Student::nextAdmissionNumber();
        $combinations = \App\Models\SubjectCombination::active()->orderBy('code')->get();
        return view('students.create', compact('classes', 'academicYear', 'nextAdmissionNumber', 'combinations'));
    }

    public function store(StoreStudentRequest $request)
    {
        $validated = $request->validated();

        // CHANGED: always generate the admission number server-side so it cannot be tampered
        // with and stays sequential even under concurrent submissions.
        $validated['admission_number'] = Student::nextAdmissionNumber();

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('students', 'public');
        }

        // CHANGED: store previous school attachment (transfer letter / report card)
        if ($request->hasFile('previous_school_attachment')) {
            $validated['previous_school_attachment'] = $request->file('previous_school_attachment')->store('students/previous-school', 'public');
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
                $class = SchoolClass::find($request->class_id);
                $isALevel = $class && $class->category() === 'a_level';
                // A-Level students must register on a subject combination.
                if ($isALevel && ! $request->filled('subject_combination_id')) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'subject_combination_id' => 'S.5/S.6 students must be assigned a subject combination.',
                    ]);
                }

                Enrollment::create([
                    'student_id' => $student->id,
                    'school_class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'academic_year_id' => $currentYear->id,
                    'subject_combination_id' => $isALevel ? $request->subject_combination_id : null,
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
        $combinations = \App\Models\SubjectCombination::active()->orderBy('code')->get();
        return view('students.edit', compact('student', 'classes', 'currentEnrollment', 'combinations'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('students', 'public');
        }

        // CHANGED: replace previous school attachment if a new file is uploaded
        if ($request->hasFile('previous_school_attachment')) {
            if ($student->previous_school_attachment) {
                Storage::disk('public')->delete($student->previous_school_attachment);
            }
            $validated['previous_school_attachment'] = $request->file('previous_school_attachment')->store('students/previous-school', 'public');
        }

        $student->update($validated);

        // A-Level combination lives on the current enrollment, not the student.
        if ($request->filled('subject_combination_id')) {
            $enrollment = $student->currentEnrollment();
            if ($enrollment && $enrollment->schoolClass?->category() === 'a_level') {
                $enrollment->update(['subject_combination_id' => $request->subject_combination_id]);
            }
        }

        return redirect()->route('students.show', $student)->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }
}
