<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected AcademicYear $academicYear;
    protected SchoolClass $class;
    protected Section $section;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('Admin');

        $this->academicYear = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $this->class = SchoolClass::create([
            'name' => 'P.1',
            'level' => 1,
        ]);

        $this->section = Section::create([
            'school_class_id' => $this->class->id,
            'name' => 'A',
        ]);

        $this->student = Student::create([
            'admission_number' => 'STU-001',
            'first_name' => 'Alice',
            'last_name' => 'Namukasa',
            'gender' => 'female',
            'date_of_birth' => '2015-03-20',
            'admission_date' => '2026-01-10',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_view_students_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('students.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_student(): void
    {
        $response = $this->actingAs($this->user)->post(route('students.store'), [
            'admission_number' => 'STU-002',
            'first_name' => 'Bob',
            'last_name' => 'Kato',
            'gender' => 'male',
            'date_of_birth' => '2014-07-10',
            'admission_date' => '2026-01-15',
            'status' => 'active',
            'school_class_id' => $this->class->id,
            'section_id' => $this->section->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', [
            'admission_number' => 'STU-002',
            'first_name' => 'Bob',
        ]);
    }

    public function test_student_can_be_enrolled(): void
    {
        Enrollment::create([
            'student_id' => $this->student->id,
            'school_class_id' => $this->class->id,
            'section_id' => $this->section->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $this->student->id,
            'school_class_id' => $this->class->id,
            'status' => 'active',
        ]);
    }

    public function test_student_cannot_be_enrolled_twice_in_same_class_year(): void
    {
        Enrollment::create([
            'student_id' => $this->student->id,
            'school_class_id' => $this->class->id,
            'section_id' => $this->section->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        $existingCount = Enrollment::where('student_id', $this->student->id)
            ->where('school_class_id', $this->class->id)
            ->where('academic_year_id', $this->academicYear->id)
            ->count();

        $this->assertEquals(1, $existingCount);
    }

    public function test_admin_can_view_student_details(): void
    {
        $response = $this->actingAs($this->user)->get(route('students.show', $this->student));
        $response->assertStatus(200);
        $response->assertSee('Alice');
    }

    public function test_admin_can_update_student(): void
    {
        $response = $this->actingAs($this->user)->put(route('students.update', $this->student), [
            'admission_number' => $this->student->admission_number,
            'first_name' => 'Alice Updated',
            'last_name' => 'Namukasa',
            'gender' => 'female',
            'date_of_birth' => '2015-03-20',
            'admission_date' => '2026-01-10',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->student->refresh();
        $this->assertEquals('Alice Updated', $this->student->first_name);
    }

    public function test_student_admission_number_must_be_unique(): void
    {
        $response = $this->actingAs($this->user)->post(route('students.store'), [
            'admission_number' => 'STU-001', // Already exists
            'first_name' => 'Duplicate',
            'last_name' => 'Student',
            'gender' => 'male',
            'date_of_birth' => '2015-01-01',
            'admission_date' => '2026-01-15',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('admission_number');
    }

    public function test_unauthorized_user_cannot_create_student(): void
    {
        $unauthorizedUser = User::factory()->create();

        $response = $this->actingAs($unauthorizedUser)->post(route('students.store'), [
            'admission_number' => 'STU-003',
            'first_name' => 'Unauthorized',
            'last_name' => 'User',
            'gender' => 'male',
            'date_of_birth' => '2015-01-01',
            'admission_date' => '2026-01-15',
            'status' => 'active',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_student(): void
    {
        $response = $this->actingAs($this->user)->delete(route('students.destroy', $this->student));
        $response->assertRedirect();
        $this->assertDatabaseMissing('students', ['id' => $this->student->id]);
    }
}
