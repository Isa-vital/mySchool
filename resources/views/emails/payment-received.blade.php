<x-mail::message>
    # Payment Receipt

    Dear {{ $payment->student->full_name ?? 'Parent/Guardian' }},

    We have received your payment. Here are the details:

    **Receipt Number:** {{ $payment->receipt_number }}
    **Amount:** {{ setting('currency_symbol', 'UGX') }} {{ number_format($payment->amount) }}
    **Payment Method:** {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
    **Date:** {{ $payment->payment_date->format('d M Y') }}

    @if($payment->invoice)
    **Invoice:** {{ $payment->invoice->invoice_number }}
    **Remaining Balance:** {{ setting('currency_symbol', 'UGX') }} {{ number_format($payment->invoice->balance) }}
    @endif

    @if($payment->reference)
    **Reference:** {{ $payment->reference }}
    @endif

    Thank you for your payment.

    Regards,
    {{ setting('school_name', config('app.name')) }}
</x-mail::message>