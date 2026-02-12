<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['student', 'invoice']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('student', fn($sq) => $sq->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('from_date')) {
            $query->where('payment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('payment_date', '<=', $request->to_date);
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(20)->withQueryString();
        return view('payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        $students = Student::active()->orderBy('first_name')->get();
        $selectedStudentId = $request->get('student_id');
        $invoices = collect();

        if ($selectedStudentId) {
            $invoices = Invoice::where('student_id', $selectedStudentId)
                ->whereIn('status', ['unpaid', 'partial'])
                ->get();
        }

        return view('payments.create', compact('students', 'invoices', 'selectedStudentId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'reference' => 'nullable|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $prefix = setting('receipt_prefix', 'RCT');
        $lastPayment = Payment::orderBy('id', 'desc')->first();
        $nextNumber = $lastPayment ? ((int)substr($lastPayment->receipt_number, strlen($prefix)) + 1) : 1;

        $payment = Payment::create([
            'receipt_number' => $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT),
            'student_id' => $request->student_id,
            'invoice_id' => $request->invoice_id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'reference' => $request->reference,
            'payment_date' => $request->payment_date,
            'notes' => $request->notes,
            'received_by' => auth()->id(),
        ]);

        // Update invoice if linked
        if ($request->invoice_id) {
            $invoice = Invoice::find($request->invoice_id);
            if ($invoice) {
                $totalPaid = Payment::where('invoice_id', $invoice->id)->sum('amount');
                $invoice->update([
                    'amount_paid' => $totalPaid,
                    'balance' => $invoice->total_amount - $totalPaid,
                    'status' => $totalPaid >= $invoice->total_amount ? 'paid' : 'partial',
                ]);
            }
        }

        return redirect()->route('payments.index')->with('success', 'Payment recorded successfully.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['student', 'invoice', 'receivedBy']);
        return view('payments.show', compact('payment'));
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['student', 'invoice', 'receivedBy']);
        $pdf = Pdf::loadView('payments.receipt-pdf', compact('payment'));
        return $pdf->download("receipt-{$payment->receipt_number}.pdf");
    }

    public function destroy(Payment $payment)
    {
        $invoiceId = $payment->invoice_id;
        $payment->delete();

        // Recalculate invoice
        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if ($invoice) {
                $totalPaid = Payment::where('invoice_id', $invoice->id)->sum('amount');
                $invoice->update([
                    'amount_paid' => $totalPaid,
                    'balance' => $invoice->total_amount - $totalPaid,
                    'status' => $totalPaid >= $invoice->total_amount ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid'),
                ]);
            }
        }

        return redirect()->route('payments.index')->with('success', 'Payment deleted successfully.');
    }
}
