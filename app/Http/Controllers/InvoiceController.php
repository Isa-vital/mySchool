<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Student;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Enrollment;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\GenerateBulkInvoicesRequest;
use App\Mail\InvoiceCreatedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['student', 'academicYear', 'term']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('student', fn($sq) => $sq->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $students = Student::active()->orderBy('first_name')->get();
        $academicYears = AcademicYear::with('terms')->orderBy('start_date', 'desc')->get();
        return view('invoices.create', compact('students', 'academicYears'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $validated = $request->validated();

        $prefix = setting('invoice_prefix', 'INV');
        $lastInvoice = Invoice::orderBy('id', 'desc')->first();
        $nextNumber = $lastInvoice ? ((int)substr($lastInvoice->invoice_number, strlen($prefix)) + 1) : 1;
        $invoiceNumber = $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        $totalAmount = collect($request->items)->sum('amount');

        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'student_id' => $request->student_id,
            'academic_year_id' => $request->academic_year_id,
            'term_id' => $request->term_id,
            'total_amount' => $totalAmount,
            'amount_paid' => 0,
            'balance' => $totalAmount,
            'status' => 'unpaid',
            'due_date' => $request->due_date,
            'notes' => $request->notes,
        ]);

        foreach ($request->items as $item) {
            $invoice->items()->create($item);
        }

        // Send invoice email to guardian
        $invoice->load(['student', 'academicYear', 'term', 'items.feeType']);
        $student = $invoice->student;
        if ($student) {
            $guardian = $student->primaryGuardian();
            $email = $guardian?->email ?? $student->email;
            if ($email) {
                Mail::to($email)->queue(new InvoiceCreatedMail($invoice));
            }
        }

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['student', 'academicYear', 'term', 'items.feeType']);
        $payments = $invoice->student->payments()->where('invoice_id', $invoice->id)->get();
        return view('invoices.show', compact('invoice', 'payments'));
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    public function generateBulk(GenerateBulkInvoicesRequest $request)
    {
        $validated = $request->validated();

        \App\Jobs\GenerateBulkInvoicesJob::dispatch(
            $request->academic_year_id,
            $request->class_id,
            $request->term_id,
            auth()->id(),
        );

        return redirect()->route('invoices.index')->with('success', 'Bulk invoice generation has been queued and will be processed shortly.');
    }
}
