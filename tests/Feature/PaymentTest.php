<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Student $student;
    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('Admin');

        $academicYear = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Term 1',
            'start_date' => '2026-01-01',
            'end_date' => '2026-04-30',
        ]);

        $this->student = Student::create([
            'admission_number' => 'STU-001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'gender' => 'female',
            'date_of_birth' => '2015-06-15',
            'admission_date' => '2026-01-15',
            'status' => 'active',
        ]);

        $feeType = FeeType::create(['name' => 'Tuition', 'is_active' => true]);

        $this->invoice = Invoice::create([
            'invoice_number' => 'INV-000001',
            'student_id' => $this->student->id,
            'academic_year_id' => $academicYear->id,
            'term_id' => $term->id,
            'total_amount' => 500000,
            'amount_paid' => 0,
            'balance' => 500000,
            'status' => 'unpaid',
        ]);

        InvoiceItem::create([
            'invoice_id' => $this->invoice->id,
            'fee_type_id' => $feeType->id,
            'amount' => 500000,
        ]);
    }

    public function test_admin_can_view_payments_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('payments.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_record_payment(): void
    {
        $response = $this->actingAs($this->user)->post(route('payments.store'), [
            'invoice_id' => $this->invoice->id,
            'student_id' => $this->student->id,
            'amount' => 200000,
            'payment_method' => 'cash',
            'payment_date' => '2026-02-01',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $this->invoice->id,
            'amount' => 200000,
            'payment_method' => 'cash',
        ]);

        $this->invoice->refresh();
        $this->assertEquals('partial', $this->invoice->status);
    }

    public function test_payment_cannot_exceed_invoice_balance(): void
    {
        $response = $this->actingAs($this->user)->post(route('payments.store'), [
            'invoice_id' => $this->invoice->id,
            'student_id' => $this->student->id,
            'amount' => 999999,
            'payment_method' => 'cash',
            'payment_date' => '2026-02-01',
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_payment_generates_receipt_number(): void
    {
        $this->actingAs($this->user)->post(route('payments.store'), [
            'invoice_id' => $this->invoice->id,
            'student_id' => $this->student->id,
            'amount' => 100000,
            'payment_method' => 'mobile_money',
            'payment_date' => '2026-02-01',
            'reference' => 'MM-12345',
        ]);

        $payment = Payment::first();
        $this->assertNotNull($payment);
        // CHANGED (tests): receipt numbers use the configurable receipt_prefix setting
        // (default 'RCT'), e.g. RCT000001 — not the old hardcoded 'RCP-'.
        // $this->assertStringStartsWith('RCP-', $payment->receipt_number);
        $this->assertStringStartsWith(setting('receipt_prefix', 'RCT'), $payment->receipt_number);
    }

    public function test_full_payment_marks_invoice_as_paid(): void
    {
        $this->actingAs($this->user)->post(route('payments.store'), [
            'invoice_id' => $this->invoice->id,
            'student_id' => $this->student->id,
            'amount' => 500000,
            'payment_method' => 'bank',
            'payment_date' => '2026-02-01',
        ]);

        $this->invoice->refresh();
        $this->assertEquals('paid', $this->invoice->status);
        $this->assertEquals(0, $this->invoice->balance);
    }

    public function test_admin_can_view_payment_details(): void
    {
        $payment = Payment::create([
            'receipt_number' => 'RCP-000001',
            'invoice_id' => $this->invoice->id,
            'student_id' => $this->student->id,
            'amount' => 100000,
            'payment_method' => 'cash',
            'payment_date' => now(),
            'received_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('payments.show', $payment));
        $response->assertStatus(200);
    }

    public function test_unauthorized_user_cannot_record_payment(): void
    {
        $unauthorizedUser = User::factory()->create();

        $response = $this->actingAs($unauthorizedUser)->post(route('payments.store'), [
            'invoice_id' => $this->invoice->id,
            'student_id' => $this->student->id,
            'amount' => 100000,
            'payment_method' => 'cash',
            'payment_date' => '2026-02-01',
        ]);

        $response->assertStatus(403);
    }

    public function test_payment_requires_valid_payment_method(): void
    {
        $response = $this->actingAs($this->user)->post(route('payments.store'), [
            'invoice_id' => $this->invoice->id,
            'student_id' => $this->student->id,
            'amount' => 100000,
            'payment_method' => 'bitcoin',
            'payment_date' => '2026-02-01',
        ]);

        $response->assertSessionHasErrors('payment_method');
    }
}
