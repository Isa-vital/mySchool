<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions by module
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Settings
            'settings.view', 'settings.edit',

            // Users & Roles
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',

            // Students
            'students.view', 'students.create', 'students.edit', 'students.delete',
            'students.enroll', 'students.promote',

            // Staff
            'staff.view', 'staff.create', 'staff.edit', 'staff.delete',

            // Guardians
            'guardians.view', 'guardians.create', 'guardians.edit', 'guardians.delete',

            // Academics
            'academic_years.view', 'academic_years.create', 'academic_years.edit', 'academic_years.delete',
            'classes.view', 'classes.create', 'classes.edit', 'classes.delete',
            'sections.view', 'sections.create', 'sections.edit', 'sections.delete',
            'subjects.view', 'subjects.create', 'subjects.edit', 'subjects.delete',
            'timetable.view', 'timetable.create', 'timetable.edit', 'timetable.delete',

            // Exams & Grades
            'exams.view', 'exams.create', 'exams.edit', 'exams.delete',
            'grades.view', 'grades.create', 'grades.edit',
            'report_cards.view', 'report_cards.generate',

            // Attendance
            'attendance.view', 'attendance.mark', 'attendance.edit',

            // Finance
            'fee_types.view', 'fee_types.create', 'fee_types.edit', 'fee_types.delete',
            'fee_structures.view', 'fee_structures.create', 'fee_structures.edit', 'fee_structures.delete',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete',
            'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
            'finance_reports.view',

            // Communication
            'notices.view', 'notices.create', 'notices.edit', 'notices.delete',
            'messages.view', 'messages.send',
            'notifications.send',

            // Library
            'books.view', 'books.create', 'books.edit', 'books.delete',
            'book_issues.view', 'book_issues.create', 'book_issues.return',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create default Super Admin role with all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Create a default Admin role
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions(Permission::all());

        // Create default Teacher role
        $teacher = Role::firstOrCreate(['name' => 'Teacher']);
        $teacher->syncPermissions([
            'dashboard.view',
            'students.view',
            'attendance.view', 'attendance.mark', 'attendance.edit',
            'classes.view', 'sections.view', 'subjects.view',
            'timetable.view',
            'exams.view', 'grades.view', 'grades.create', 'grades.edit',
            'report_cards.view',
            'notices.view',
            'messages.view', 'messages.send',
            'books.view', 'book_issues.view',
        ]);

        // Create default Bursar role
        $bursar = Role::firstOrCreate(['name' => 'Bursar']);
        $bursar->syncPermissions([
            'dashboard.view',
            'students.view',
            'fee_types.view', 'fee_types.create', 'fee_types.edit',
            'fee_structures.view', 'fee_structures.create', 'fee_structures.edit',
            'invoices.view', 'invoices.create', 'invoices.edit',
            'payments.view', 'payments.create', 'payments.edit',
            'finance_reports.view',
            'notices.view',
            'messages.view', 'messages.send',
        ]);

        // Create default Librarian role
        $librarian = Role::firstOrCreate(['name' => 'Librarian']);
        $librarian->syncPermissions([
            'dashboard.view',
            'students.view', 'staff.view',
            'books.view', 'books.create', 'books.edit', 'books.delete',
            'book_issues.view', 'book_issues.create', 'book_issues.return',
            'notices.view',
            'messages.view', 'messages.send',
        ]);
    }
}
