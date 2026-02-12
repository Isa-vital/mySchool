<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $payment->receipt_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e40af; padding-bottom: 15px; }
        .header h1 { font-size: 18px; color: #1e40af; }
        .header p { font-size: 10px; color: #666; margin-top: 3px; }
        .header .title { font-size: 14px; font-weight: bold; margin-top: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .receipt-no { text-align: right; font-size: 12px; margin-bottom: 15px; }
        .receipt-no strong { color: #1e40af; font-size: 14px; }
        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-row { display: table-row; }
        .info-cell { display: table-cell; width: 50%; padding: 4px 0; }
        .info-cell .label { color: #888; font-size: 9px; text-transform: uppercase; }
        .info-cell .value { font-weight: bold; font-size: 11px; }
        .amount-box { text-align: center; margin: 25px 0; padding: 15px; background: #f0f4ff; border: 1px solid #c7d2fe; border-radius: 4px; }
        .amount-box .amount { font-size: 24px; font-weight: bold; color: #1e40af; }
        .amount-box .label { font-size: 9px; color: #888; text-transform: uppercase; margin-bottom: 5px; }
        table.details { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.details th { background: #f3f4f6; padding: 8px 10px; font-size: 10px; text-transform: uppercase; text-align: left; border-bottom: 1px solid #d1d5db; }
        table.details td { padding: 8px 10px; font-size: 11px; border-bottom: 1px solid #e5e7eb; }
        .notes { margin-top: 15px; padding: 10px; background: #fefce8; border-left: 3px solid #eab308; font-size: 10px; }
        .footer { margin-top: 40px; display: table; width: 100%; }
        .sig-block { display: table-cell; width: 50%; text-align: center; padding-top: 30px; }
        .sig-line { border-top: 1px solid #333; width: 70%; margin: 0 auto; }
        .sig-label { font-size: 9px; color: #666; margin-top: 4px; }
        .watermark { text-align: center; color: #d1fae5; font-size: 48px; font-weight: bold; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); opacity: 0.3; z-index: -1; }
        .print-notice { text-align: center; margin-top: 30px; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ setting('school_name', 'MySchool') }}</h1>
        <p>{{ setting('school_address', '') }}</p>
        <p>{{ setting('school_phone', '') }} | {{ setting('school_email', '') }}</p>
        <div class="title">Payment Receipt</div>
    </div>

    <div class="receipt-no">
        Receipt No: <strong>{{ $payment->receipt_number }}</strong><br>
        <span style="font-size: 10px; color: #666;">Date: {{ $payment->payment_date?->format('d M Y') ?? now()->format('d M Y') }}</span>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell">
                <span class="label">Received From:</span><br>
                <span class="value">{{ $payment->student->full_name ?? $payment->student->first_name . ' ' . $payment->student->last_name }}</span>
            </div>
            <div class="info-cell">
                <span class="label">Admission No:</span><br>
                <span class="value">{{ $payment->student->admission_number ?? '-' }}</span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-cell">
                <span class="label">Payment Method:</span><br>
                <span class="value" style="text-transform: capitalize;">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
            </div>
            <div class="info-cell">
                <span class="label">Reference:</span><br>
                <span class="value">{{ $payment->reference ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <div class="amount-box">
        <div class="label">Amount Paid</div>
        <div class="amount">UGX {{ number_format($payment->amount, 0) }}</div>
    </div>

    @if($payment->invoice)
    <table class="details">
        <thead>
            <tr>
                <th>Invoice Details</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Invoice #{{ $payment->invoice->invoice_number }}</strong><br>
                    <span style="color: #666; font-size: 10px;">{{ $payment->invoice->description ?? 'School Fees' }}</span>
                </td>
                <td style="text-align: right;">UGX {{ number_format($payment->invoice->total_amount, 0) }}</td>
            </tr>
            <tr>
                <td><strong>Total Paid on Invoice</strong></td>
                <td style="text-align: right; color: #16a34a;"><strong>UGX {{ number_format($payment->invoice->amount_paid, 0) }}</strong></td>
            </tr>
            <tr>
                <td><strong>Remaining Balance</strong></td>
                <td style="text-align: right; color: {{ $payment->invoice->balance > 0 ? '#dc2626' : '#16a34a' }};">
                    <strong>UGX {{ number_format($payment->invoice->balance, 0) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>
    @endif

    @if($payment->notes)
    <div class="notes">
        <strong>Notes:</strong> {{ $payment->notes }}
    </div>
    @endif

    <div class="footer">
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Received By: {{ $payment->receivedBy->name ?? 'Admin' }}</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Parent / Guardian Signature</div>
        </div>
    </div>

    <div class="print-notice">
        This is a computer-generated receipt. | {{ setting('school_name', 'MySchool') }} &copy; {{ date('Y') }}
    </div>
</body>
</html>
