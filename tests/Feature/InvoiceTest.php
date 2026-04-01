<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected AcademicYear $academicYear;
    protected Student $student;
    protected SchoolClass $class;
    protected Term $term;
    protected FeeType $feeType;

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

        $this->term = Term::create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Term 1',
            'start_date' => '2026-01-01',
            'end_date' => '2026-04-30',
        ]);

        $this->class = SchoolClass::create([
            'name' => 'P.1',
            'level' => 1,
        ]);

        $this->student = Student::create([
            'admission_number' => 'STU-001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'male',
            'date_of_birth' => '2015-01-01',
            'admission_date' => '2026-01-15',
            'status' => 'active',
        ]);

        $this->feeType = FeeType::create([
            'name' => 'Tuition',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_invoices_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('invoices.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_invoice(): void
    {
        $response = $this->actingAs($this->user)->post(route('invoices.store'), [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'due_date' => '2026-02-28',
            'items' => [
                ['fee_type_id' => $this->feeType->id, 'amount' => 500000],
            ],
        ]);

        $response->assertRedirect(route('invoices.index'));
        $this->assertDatabaseHas('invoices', [
            'student_id' => $this->student->id,
            'status' => 'unpaid',
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'fee_type_id' => $this->feeType->id,
            'amount' => 500000,
        ]);
    }

    public function test_invoice_number_is_auto_generated(): void
    {
        $this->actingAs($this->user)->post(route('invoices.store'), [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'due_date' => '2026-02-28',
            'items' => [
                ['fee_type_id' => $this->feeType->id, 'amount' => 100000],
            ],
        ]);

        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
    }

    public function test_invoice_recalculates_on_payment(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-000001',
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'total_amount' => 500000,
            'amount_paid' => 0,
            'balance' => 500000,
            'status' => 'unpaid',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'fee_type_id' => $this->feeType->id,
            'amount' => 500000,
        ]);

        $invoice->payments()->create([
            'receipt_number' => 'RCP-000001',
            'student_id' => $this->student->id,
            'amount' => 200000,
            'payment_method' => 'cash',
            'payment_date' => now(),
        ]);

        $invoice->recalculate();

        $this->assertEquals('partial', $invoice->status);
        $this->assertEquals(200000, $invoice->amount_paid);
        $this->assertEquals(300000, $invoice->balance);
    }

    public function test_invoice_status_becomes_paid_when_fully_paid(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-000002',
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'total_amount' => 500000,
            'amount_paid' => 0,
            'balance' => 500000,
            'status' => 'unpaid',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'fee_type_id' => $this->feeType->id,
            'amount' => 500000,
        ]);

        $invoice->payments()->create([
            'receipt_number' => 'RCP-000003',
            'student_id' => $this->student->id,
            'amount' => 500000,
            'payment_method' => 'bank',
            'payment_date' => now(),
        ]);

        $invoice->recalculate();

        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(0, $invoice->balance);
    }

    public function test_admin_can_delete_invoice(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-000004',
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'total_amount' => 100000,
            'amount_paid' => 0,
            'balance' => 100000,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->user)->delete(route('invoices.destroy', $invoice));
        $response->assertRedirect(route('invoices.index'));
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_unauthorized_user_cannot_view_invoices(): void
    {
        $unauthorizedUser = User::factory()->create();
        $response = $this->actingAs($unauthorizedUser)->get(route('invoices.index'));
        $response->assertStatus(403);
    }

    public function test_create_invoice_requires_valid_student(): void
    {
        $response = $this->actingAs($this->user)->post(route('invoices.store'), [
            'student_id' => 9999,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'due_date' => '2026-02-28',
            'items' => [
                ['fee_type_id' => $this->feeType->id, 'amount' => 100000],
            ],
        ]);

        $response->assertSessionHasErrors('student_id');
    }
}
