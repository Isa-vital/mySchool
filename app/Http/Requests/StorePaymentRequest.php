<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01|max:99999999.99',
            'payment_method' => 'required|string|in:cash,bank,mobile_money,cheque',
            'reference' => 'nullable|string|max:255',
            'payment_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
