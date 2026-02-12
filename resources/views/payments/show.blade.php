<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Receipt: {{ $payment->receipt_number }}</h2>
            <div class="flex items-center space-x-3">
                <a href="{{ route('payments.receipt', $payment) }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-green-600 hover:bg-green-700">Download PDF</a>
                <a href="{{ route('payments.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border p-8">
            {{-- Receipt Header --}}
            <div class="text-center mb-6 pb-6 border-b">
                <h1 class="text-2xl font-bold text-gray-900">{{ setting('school_name', 'MySchool') }}</h1>
                <p class="text-sm text-gray-500">{{ setting('school_address', '') }}</p>
                <p class="text-lg font-semibold mt-3" style="color: var(--primary-color);">PAYMENT RECEIPT</p>
            </div>

            {{-- Receipt Details --}}
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Receipt Number</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $payment->receipt_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase">Date</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $payment->payment_date?->format('d M Y') ?? $payment->created_at->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Student</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $payment->student->full_name ?? '-' }}</p>
                    <p class="text-xs text-gray-500">{{ $payment->student->admission_number ?? '' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase">Payment Method</p>
                    <p class="text-sm font-semibold text-gray-900 capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</p>
                </div>
            </div>

            @if($payment->invoice)
                <div class="mb-6 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase mb-1">For Invoice</p>
                    <p class="text-sm text-gray-900">{{ $payment->invoice->invoice_number }}</p>
                </div>
            @endif

            {{-- Amount --}}
            <div class="bg-green-50 rounded-xl p-6 text-center mb-6">
                <p class="text-sm text-green-600 uppercase font-medium">Amount Paid</p>
                <p class="text-3xl font-bold text-green-700 mt-1">{{ setting('currency_symbol', 'UGX') }} {{ number_format($payment->amount) }}</p>
            </div>

            @if($payment->reference)
                <div class="mb-4">
                    <p class="text-xs text-gray-500 uppercase">Reference / Transaction ID</p>
                    <p class="text-sm text-gray-900">{{ $payment->reference }}</p>
                </div>
            @endif

            @if($payment->notes)
                <div class="mb-4">
                    <p class="text-xs text-gray-500 uppercase">Notes</p>
                    <p class="text-sm text-gray-900">{{ $payment->notes }}</p>
                </div>
            @endif

            <div class="mt-6 pt-4 border-t text-xs text-gray-400">
                Received by: {{ $payment->receivedBy->name ?? 'System' }} &bull; Printed: {{ now()->format('d M Y H:i') }}
            </div>
        </div>
    </div>
</x-app-layout>
