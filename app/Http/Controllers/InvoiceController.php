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
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.fee_type_id' => 'required|exists:fee_types,id',
            'items.*.description' => 'nullable|string',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

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

    public function generateBulk(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
        ]);

        $currentYear = AcademicYear::find($request->academic_year_id);
        $enrollments = Enrollment::where('school_class_id', $request->class_id)
            ->where('academic_year_id', $currentYear->id)
            ->where('status', 'active')
            ->get();

        $feeStructures = FeeStructure::where('school_class_id', $request->class_id)
            ->where('academic_year_id', $currentYear->id)
            ->when($request->term_id, fn($q) => $q->where('term_id', $request->term_id))
            ->get();

        $prefix = setting('invoice_prefix', 'INV');
        $lastInvoice = Invoice::orderBy('id', 'desc')->first();
        $nextNumber = $lastInvoice ? ((int)substr($lastInvoice->invoice_number, strlen($prefix)) + 1) : 1;
        $count = 0;

        foreach ($enrollments as $enrollment) {
            $existing = Invoice::where('student_id', $enrollment->student_id)
                ->where('academic_year_id', $currentYear->id)
                ->when($request->term_id, fn($q) => $q->where('term_id', $request->term_id))
                ->exists();

            if ($existing) continue;

            $totalAmount = $feeStructures->sum('amount');
            $invoiceNumber = $prefix . str_pad($nextNumber++, 6, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'student_id' => $enrollment->student_id,
                'academic_year_id' => $currentYear->id,
                'term_id' => $request->term_id,
                'total_amount' => $totalAmount,
                'amount_paid' => 0,
                'balance' => $totalAmount,
                'status' => 'unpaid',
            ]);

            foreach ($feeStructures as $fee) {
                $invoice->items()->create([
                    'fee_type_id' => $fee->fee_type_id,
                    'description' => $fee->feeType->name,
                    'amount' => $fee->amount,
                ]);
            }

            $count++;
        }

        return redirect()->route('invoices.index')->with('success', "{$count} invoices generated successfully.");
    }
}
