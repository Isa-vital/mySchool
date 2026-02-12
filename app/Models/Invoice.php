<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'student_id', 'academic_year_id', 'term_id',
        'total_amount', 'amount_paid', 'balance', 'status', 'due_date', 'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function recalculate(): void
    {
        $this->total_amount = $this->items()->sum('amount');
        $this->amount_paid = $this->payments()->sum('amount');
        $this->balance = $this->total_amount - $this->amount_paid;
        $this->status = match (true) {
            $this->balance <= 0 => 'paid',
            $this->amount_paid > 0 => 'partial',
            default => 'unpaid',
        };
        $this->save();
    }

    public static function generateInvoiceNumber(): string
    {
        $last = static::latest('id')->first();
        $number = $last ? (int) substr($last->invoice_number, 4) + 1 : 1;
        return 'INV-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
