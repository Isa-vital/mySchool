<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use LogsActivity;
    protected $fillable = [
        'receipt_number',
        'invoice_id',
        'student_id',
        'amount',
        'payment_method',
        'reference',
        'payment_date',
        'notes',
        'received_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public static function generateReceiptNumber(): string
    {
        $last = static::latest('id')->first();
        $number = $last ? (int) substr($last->receipt_number, 4) + 1 : 1;
        return 'RCP-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
