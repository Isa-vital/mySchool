<x-mail::message>
    # New Invoice

    Dear {{ $invoice->student->full_name ?? 'Parent/Guardian' }},

    A new invoice has been generated for your account. Please find the details below:

    **Invoice Number:** {{ $invoice->invoice_number }}
    **Academic Year:** {{ $invoice->academicYear->name ?? '' }}
    @if($invoice->term)
    **Term:** {{ $invoice->term->name }}
    @endif
    **Total Amount:** {{ setting('currency_symbol', 'UGX') }} {{ number_format($invoice->total_amount) }}
    @if($invoice->due_date)
    **Due Date:** {{ $invoice->due_date->format('d M Y') }}
    @endif

    **Items:**
    @foreach($invoice->items as $item)
    - {{ $item->description ?? $item->feeType->name ?? 'Fee' }}: {{ setting('currency_symbol', 'UGX') }} {{ number_format($item->amount) }}
    @endforeach

    Please make payment before the due date to avoid any inconvenience.

    Regards,
    {{ setting('school_name', config('app.name')) }}
</x-mail::message>