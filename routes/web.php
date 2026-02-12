<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\FeeTypeController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookIssueController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard.view');

    // Profile (no permission needed — every user manages their own profile)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Students
    Route::middleware('permission:students.view')->group(function () {
        Route::get('students', [StudentController::class, 'index'])->name('students.index');
        Route::get('students/{student}', [StudentController::class, 'show'])->name('students.show');
    });
    Route::get('students/create', [StudentController::class, 'create'])->name('students.create')->middleware('permission:students.create');
    Route::post('students', [StudentController::class, 'store'])->name('students.store')->middleware('permission:students.create');
    Route::get('students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit')->middleware('permission:students.edit');
    Route::put('students/{student}', [StudentController::class, 'update'])->name('students.update')->middleware('permission:students.edit');
    Route::patch('students/{student}', [StudentController::class, 'update'])->middleware('permission:students.edit');
    Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy')->middleware('permission:students.delete');

    // Staff
    Route::middleware('permission:staff.view')->group(function () {
        Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('staff/{staff}', [StaffController::class, 'show'])->name('staff.show');
    });
    Route::get('staff/create', [StaffController::class, 'create'])->name('staff.create')->middleware('permission:staff.create');
    Route::post('staff', [StaffController::class, 'store'])->name('staff.store')->middleware('permission:staff.create');
    Route::get('staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit')->middleware('permission:staff.edit');
    Route::put('staff/{staff}', [StaffController::class, 'update'])->name('staff.update')->middleware('permission:staff.edit');
    Route::patch('staff/{staff}', [StaffController::class, 'update'])->middleware('permission:staff.edit');
    Route::delete('staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy')->middleware('permission:staff.delete');

    // Guardians
    Route::middleware('permission:guardians.view')->group(function () {
        Route::get('guardians', [GuardianController::class, 'index'])->name('guardians.index');
        Route::get('guardians/{guardian}', [GuardianController::class, 'show'])->name('guardians.show');
    });
    Route::get('guardians/create', [GuardianController::class, 'create'])->name('guardians.create')->middleware('permission:guardians.create');
    Route::post('guardians', [GuardianController::class, 'store'])->name('guardians.store')->middleware('permission:guardians.create');
    Route::get('guardians/{guardian}/edit', [GuardianController::class, 'edit'])->name('guardians.edit')->middleware('permission:guardians.edit');
    Route::put('guardians/{guardian}', [GuardianController::class, 'update'])->name('guardians.update')->middleware('permission:guardians.edit');
    Route::patch('guardians/{guardian}', [GuardianController::class, 'update'])->middleware('permission:guardians.edit');
    Route::delete('guardians/{guardian}', [GuardianController::class, 'destroy'])->name('guardians.destroy')->middleware('permission:guardians.delete');

    // Academic Years
    Route::middleware('permission:academic_years.view')->group(function () {
        Route::get('academic-years', [AcademicYearController::class, 'index'])->name('academic-years.index');
        Route::get('academic-years/{academic_year}', [AcademicYearController::class, 'show'])->name('academic-years.show');
    });
    Route::get('academic-years/create', [AcademicYearController::class, 'create'])->name('academic-years.create')->middleware('permission:academic_years.create');
    Route::post('academic-years', [AcademicYearController::class, 'store'])->name('academic-years.store')->middleware('permission:academic_years.create');
    Route::post('academic-years/{academicYear}/set-current', [AcademicYearController::class, 'setCurrent'])->name('academic-years.set-current')->middleware('permission:academic_years.edit');
    Route::get('academic-years/{academic_year}/edit', [AcademicYearController::class, 'edit'])->name('academic-years.edit')->middleware('permission:academic_years.edit');
    Route::put('academic-years/{academic_year}', [AcademicYearController::class, 'update'])->name('academic-years.update')->middleware('permission:academic_years.edit');
    Route::patch('academic-years/{academic_year}', [AcademicYearController::class, 'update'])->middleware('permission:academic_years.edit');
    Route::delete('academic-years/{academic_year}', [AcademicYearController::class, 'destroy'])->name('academic-years.destroy')->middleware('permission:academic_years.delete');

    // Classes & Sections
    Route::middleware('permission:classes.view')->group(function () {
        Route::get('classes', [SchoolClassController::class, 'index'])->name('classes.index');
        Route::get('classes/{class}', [SchoolClassController::class, 'show'])->name('classes.show');
    });
    Route::get('classes/create', [SchoolClassController::class, 'create'])->name('classes.create')->middleware('permission:classes.create');
    Route::post('classes', [SchoolClassController::class, 'store'])->name('classes.store')->middleware('permission:classes.create');
    Route::get('classes/{class}/edit', [SchoolClassController::class, 'edit'])->name('classes.edit')->middleware('permission:classes.edit');
    Route::put('classes/{class}', [SchoolClassController::class, 'update'])->name('classes.update')->middleware('permission:classes.edit');
    Route::patch('classes/{class}', [SchoolClassController::class, 'update'])->middleware('permission:classes.edit');
    Route::delete('classes/{class}', [SchoolClassController::class, 'destroy'])->name('classes.destroy')->middleware('permission:classes.delete');
    Route::post('classes/{class}/sections', [SchoolClassController::class, 'storeSection'])->name('classes.sections.store')->middleware('permission:sections.create');
    Route::delete('sections/{section}', [SchoolClassController::class, 'destroySection'])->name('sections.destroy')->middleware('permission:sections.delete');

    // Subjects
    Route::middleware('permission:subjects.view')->group(function () {
        Route::get('subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
    });
    Route::get('subjects/create', [SubjectController::class, 'create'])->name('subjects.create')->middleware('permission:subjects.create');
    Route::post('subjects', [SubjectController::class, 'store'])->name('subjects.store')->middleware('permission:subjects.create');
    Route::get('subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit')->middleware('permission:subjects.edit');
    Route::put('subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update')->middleware('permission:subjects.edit');
    Route::patch('subjects/{subject}', [SubjectController::class, 'update'])->middleware('permission:subjects.edit');
    Route::delete('subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy')->middleware('permission:subjects.delete');

    // Timetable
    Route::get('timetable', [TimetableController::class, 'index'])->name('timetable.index')->middleware('permission:timetable.view');
    Route::get('timetable/create', [TimetableController::class, 'create'])->name('timetable.create')->middleware('permission:timetable.create');
    Route::post('timetable', [TimetableController::class, 'store'])->name('timetable.store')->middleware('permission:timetable.create');
    Route::put('timetable/{slot}', [TimetableController::class, 'update'])->name('timetable.update')->middleware('permission:timetable.edit');
    Route::delete('timetable/{slot}', [TimetableController::class, 'destroy'])->name('timetable.destroy')->middleware('permission:timetable.delete');

    // Attendance
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index')->middleware('permission:attendance.view');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store')->middleware('permission:attendance.mark');
    Route::get('attendance/report', [AttendanceController::class, 'report'])->name('attendance.report')->middleware('permission:attendance.view');

    // Exams
    Route::middleware('permission:exams.view')->group(function () {
        Route::get('exams', [ExamController::class, 'index'])->name('exams.index');
        Route::get('exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
    });
    Route::get('exams/create', [ExamController::class, 'create'])->name('exams.create')->middleware('permission:exams.create');
    Route::post('exams', [ExamController::class, 'store'])->name('exams.store')->middleware('permission:exams.create');
    Route::get('exams/{exam}/edit', [ExamController::class, 'edit'])->name('exams.edit')->middleware('permission:exams.edit');
    Route::put('exams/{exam}', [ExamController::class, 'update'])->name('exams.update')->middleware('permission:exams.edit');
    Route::patch('exams/{exam}', [ExamController::class, 'update'])->middleware('permission:exams.edit');
    Route::delete('exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy')->middleware('permission:exams.delete');
    Route::post('exams/{exam}/schedules', [ExamController::class, 'addSchedule'])->name('exams.schedules.store')->middleware('permission:exams.edit');
    Route::delete('exam-schedules/{schedule}', [ExamController::class, 'removeSchedule'])->name('exam-schedules.destroy')->middleware('permission:exams.edit');

    // Grades
    Route::get('grades', [GradeController::class, 'index'])->name('grades.index')->middleware('permission:grades.view');
    Route::get('grades/{exam}/enter', [GradeController::class, 'enter'])->name('grades.enter')->middleware('permission:grades.create');
    Route::post('grades/{exam}/save', [GradeController::class, 'save'])->name('grades.save')->middleware('permission:grades.create');

    // Report Cards
    Route::get('report-cards', [ReportCardController::class, 'index'])->name('report-cards.index')->middleware('permission:report_cards.view');
    Route::get('report-cards/{student}/{exam}', [ReportCardController::class, 'show'])->name('report-cards.show')->middleware('permission:report_cards.view');
    Route::get('report-cards/{student}/{exam}/pdf', [ReportCardController::class, 'pdf'])->name('report-cards.pdf')->middleware('permission:report_cards.generate');

    // Fee Types
    Route::middleware('permission:fee_types.view')->group(function () {
        Route::get('fee-types', [FeeTypeController::class, 'index'])->name('fee-types.index');
        Route::get('fee-types/{fee_type}', [FeeTypeController::class, 'show'])->name('fee-types.show');
    });
    Route::get('fee-types/create', [FeeTypeController::class, 'create'])->name('fee-types.create')->middleware('permission:fee_types.create');
    Route::post('fee-types', [FeeTypeController::class, 'store'])->name('fee-types.store')->middleware('permission:fee_types.create');
    Route::get('fee-types/{fee_type}/edit', [FeeTypeController::class, 'edit'])->name('fee-types.edit')->middleware('permission:fee_types.edit');
    Route::put('fee-types/{fee_type}', [FeeTypeController::class, 'update'])->name('fee-types.update')->middleware('permission:fee_types.edit');
    Route::patch('fee-types/{fee_type}', [FeeTypeController::class, 'update'])->middleware('permission:fee_types.edit');
    Route::delete('fee-types/{fee_type}', [FeeTypeController::class, 'destroy'])->name('fee-types.destroy')->middleware('permission:fee_types.delete');

    // Fee Structures
    Route::middleware('permission:fee_structures.view')->group(function () {
        Route::get('fee-structures', [FeeStructureController::class, 'index'])->name('fee-structures.index');
        Route::get('fee-structures/{fee_structure}', [FeeStructureController::class, 'show'])->name('fee-structures.show');
    });
    Route::get('fee-structures/create', [FeeStructureController::class, 'create'])->name('fee-structures.create')->middleware('permission:fee_structures.create');
    Route::post('fee-structures', [FeeStructureController::class, 'store'])->name('fee-structures.store')->middleware('permission:fee_structures.create');
    Route::get('fee-structures/{fee_structure}/edit', [FeeStructureController::class, 'edit'])->name('fee-structures.edit')->middleware('permission:fee_structures.edit');
    Route::put('fee-structures/{fee_structure}', [FeeStructureController::class, 'update'])->name('fee-structures.update')->middleware('permission:fee_structures.edit');
    Route::patch('fee-structures/{fee_structure}', [FeeStructureController::class, 'update'])->middleware('permission:fee_structures.edit');
    Route::delete('fee-structures/{fee_structure}', [FeeStructureController::class, 'destroy'])->name('fee-structures.destroy')->middleware('permission:fee_structures.delete');

    // Invoices
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index')->middleware('permission:invoices.view');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create')->middleware('permission:invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store')->middleware('permission:invoices.create');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show')->middleware('permission:invoices.view');
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy')->middleware('permission:invoices.delete');
    Route::post('invoices/generate-bulk', [InvoiceController::class, 'generateBulk'])->name('invoices.generate-bulk')->middleware('permission:invoices.create');

    // Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index')->middleware('permission:payments.view');
    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create')->middleware('permission:payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store')->middleware('permission:payments.create');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show')->middleware('permission:payments.view');
    Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt')->middleware('permission:payments.view');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy')->middleware('permission:payments.delete');

    // Notices
    Route::middleware('permission:notices.view')->group(function () {
        Route::get('notices', [NoticeController::class, 'index'])->name('notices.index');
        Route::get('notices/{notice}', [NoticeController::class, 'show'])->name('notices.show');
    });
    Route::get('notices/create', [NoticeController::class, 'create'])->name('notices.create')->middleware('permission:notices.create');
    Route::post('notices', [NoticeController::class, 'store'])->name('notices.store')->middleware('permission:notices.create');
    Route::get('notices/{notice}/edit', [NoticeController::class, 'edit'])->name('notices.edit')->middleware('permission:notices.edit');
    Route::put('notices/{notice}', [NoticeController::class, 'update'])->name('notices.update')->middleware('permission:notices.edit');
    Route::patch('notices/{notice}', [NoticeController::class, 'update'])->middleware('permission:notices.edit');
    Route::delete('notices/{notice}', [NoticeController::class, 'destroy'])->name('notices.destroy')->middleware('permission:notices.delete');

    // Messages
    Route::get('messages', [MessageController::class, 'index'])->name('messages.index')->middleware('permission:messages.view');
    Route::get('messages/create', [MessageController::class, 'create'])->name('messages.create')->middleware('permission:messages.send');
    Route::post('messages', [MessageController::class, 'store'])->name('messages.store')->middleware('permission:messages.send');
    Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show')->middleware('permission:messages.view');

    // Library - Books
    Route::middleware('permission:books.view')->group(function () {
        Route::get('books', [BookController::class, 'index'])->name('books.index');
        Route::get('books/{book}', [BookController::class, 'show'])->name('books.show');
    });
    Route::get('books/create', [BookController::class, 'create'])->name('books.create')->middleware('permission:books.create');
    Route::post('books', [BookController::class, 'store'])->name('books.store')->middleware('permission:books.create');
    Route::get('books/{book}/edit', [BookController::class, 'edit'])->name('books.edit')->middleware('permission:books.edit');
    Route::put('books/{book}', [BookController::class, 'update'])->name('books.update')->middleware('permission:books.edit');
    Route::patch('books/{book}', [BookController::class, 'update'])->middleware('permission:books.edit');
    Route::delete('books/{book}', [BookController::class, 'destroy'])->name('books.destroy')->middleware('permission:books.delete');

    // Library - Book Issues
    Route::get('book-issues', [BookIssueController::class, 'index'])->name('book-issues.index')->middleware('permission:book_issues.view');
    Route::post('book-issues', [BookIssueController::class, 'store'])->name('book-issues.store')->middleware('permission:book_issues.create');
    Route::patch('book-issues/{bookIssue}/return', [BookIssueController::class, 'returnBook'])->name('book-issues.return')->middleware('permission:book_issues.return');

    // Administration - Users
    Route::middleware('permission:users.view')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    });
    Route::get('users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store')->middleware('permission:users.create');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:users.edit');
    Route::patch('users/{user}', [UserController::class, 'update'])->middleware('permission:users.edit');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:users.delete');

    // Administration - Roles
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    });
    Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('permission:roles.create');
    Route::post('roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:roles.create');
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('permission:roles.edit');
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('permission:roles.edit');
    Route::patch('roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.edit');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:roles.delete');

    // Administration - Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index')->middleware('permission:settings.view');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update')->middleware('permission:settings.edit');
});

require __DIR__.'/auth.php';
