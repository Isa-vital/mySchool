<?php

namespace App\Jobs;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateBulkInvoicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $academicYearId,
        protected int $classId,
        protected ?int $termId,
        protected int $userId,
    ) {}

    public function handle(): void
    {
        $currentYear = AcademicYear::findOrFail($this->academicYearId);

        $enrollments = Enrollment::where('school_class_id', $this->classId)
            ->where('academic_year_id', $currentYear->id)
            ->where('status', 'active')
            ->get();

        $feeStructures = FeeStructure::where('school_class_id', $this->classId)
            ->where('academic_year_id', $currentYear->id)
            ->when($this->termId, fn($q) => $q->where('term_id', $this->termId))
            ->get();

        $prefix = setting('invoice_prefix', 'INV');
        $lastInvoice = Invoice::orderBy('id', 'desc')->first();
        $nextNumber = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, strlen($prefix)) + 1) : 1;

        foreach ($enrollments as $enrollment) {
            $existing = Invoice::where('student_id', $enrollment->student_id)
                ->where('academic_year_id', $currentYear->id)
                ->when($this->termId, fn($q) => $q->where('term_id', $this->termId))
                ->exists();

            if ($existing) {
                continue;
            }

            $totalAmount = $feeStructures->sum('amount');
            $invoiceNumber = $prefix . str_pad($nextNumber++, 6, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'student_id' => $enrollment->student_id,
                'academic_year_id' => $currentYear->id,
                'term_id' => $this->termId,
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
        }
    }
}
