<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Invoice: {{ $invoice->invoice_number }}</h2>
            <div class="flex items-center space-x-3">
                @if($invoice->balance > 0)
                <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-green-600 hover:bg-green-700">Record Payment</a>
                @endif
                <a href="{{ route('invoices.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border p-8">
            {{-- Invoice Header --}}
            <div class="flex items-start justify-between mb-8 pb-6 border-b">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ setting('school_name', 'MySchool') }}</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ setting('school_address', '') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold" style="color: var(--primary-color);">INVOICE</p>
                    <p class="text-sm text-gray-600 mt-1"># {{ $invoice->invoice_number }}</p>
                    <p class="text-sm text-gray-500">Date: {{ $invoice->created_at->format('d M Y') }}</p>
                    @if($invoice->due_date)
                        <p class="text-sm text-gray-500">Due: {{ $invoice->due_date->format('d M Y') }}</p>
                    @endif
                </div>
            </div>

            {{-- Bill To --}}
            <div class="mb-6">
                <p class="text-sm text-gray-500 uppercase font-medium mb-1">Bill To</p>
                <p class="text-gray-900 font-medium">{{ $invoice->student->full_name ?? '-' }}</p>
                <p class="text-sm text-gray-600">{{ $invoice->student->admission_number ?? '' }}</p>
            </div>

            {{-- Items --}}
            <table class="min-w-full divide-y divide-gray-200 mb-6">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $item->feeType->name ?? '' }} {{ $item->description ? '- ' . $item->description : '' }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ setting('currency_symbol', 'UGX') }} {{ number_format($item->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50">
                        <td class="px-4 py-3 text-sm font-bold text-gray-900">Total</td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-gray-900">{{ setting('currency_symbol', 'UGX') }} {{ number_format($invoice->total_amount) }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-600">Paid</td>
                        <td class="px-4 py-2 text-sm text-right text-green-600">{{ setting('currency_symbol', 'UGX') }} {{ number_format($invoice->amount_paid) }}</td>
                    </tr>
                    <tr class="border-t-2 border-gray-300">
                        <td class="px-4 py-3 text-sm font-bold text-gray-900">Balance Due</td>
                        <td class="px-4 py-3 text-sm text-right font-bold {{ $invoice->balance > 0 ? 'text-red-600' : 'text-green-600' }}">{{ setting('currency_symbol', 'UGX') }} {{ number_format($invoice->balance) }}</td>
                    </tr>
                </tfoot>
            </table>

            {{-- Payment History --}}
            @if($payments->count())
                <h3 class="text-sm font-semibold text-gray-800 mb-2">Payment History</h3>
                <table class="min-w-full divide-y divide-gray-200 mb-4">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Receipt #</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Method</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($payments as $payment)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-600">{{ $payment->receipt_number }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600">{{ $payment->payment_date?->format('d M Y') ?? $payment->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600 capitalize">{{ $payment->payment_method }}</td>
                                <td class="px-4 py-2 text-sm text-right text-green-600">{{ setting('currency_symbol', 'UGX') }} {{ number_format($payment->amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-app-layout>
