<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('parent.child', $student) }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Fees & Invoices &mdash; {{ $student->full_name }}</h2>
        </div>
    </x-slot>

    @php
    $totalDue = $invoices->whereIn('status', ['unpaid', 'partial'])->sum('balance');
    $totalPaid = $invoices->sum('paid_amount');
    @endphp

    {{-- Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
            <p class="text-sm text-gray-500">Total Invoiced</p>
            <p class="text-xl font-bold text-gray-800 mt-1">{{ setting('currency_symbol', 'UGX') }} {{ number_format($invoices->sum('total_amount')) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
            <p class="text-sm text-gray-500">Total Paid</p>
            <p class="text-xl font-bold text-green-600 mt-1">{{ setting('currency_symbol', 'UGX') }} {{ number_format($totalPaid) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
            <p class="text-sm text-gray-500">Balance Due</p>
            <p class="text-xl font-bold {{ $totalDue > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">{{ setting('currency_symbol', 'UGX') }} {{ number_format($totalDue) }}</p>
        </div>
    </div>

    {{-- Invoices List --}}
    <div class="space-y-4">
        @forelse($invoices as $invoice)
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <div>
                    <h4 class="font-semibold text-gray-800">{{ $invoice->invoice_number }}</h4>
                    <p class="text-sm text-gray-500">{{ $invoice->term->name ?? '' }} &mdash; {{ $invoice->academicYear->name ?? '' }}</p>
                </div>
                <span class="px-3 py-1 text-xs font-bold rounded-full
                        {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $invoice->status === 'partial' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $invoice->status === 'unpaid' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $invoice->status === 'cancelled' ? 'bg-gray-100 text-gray-500' : '' }}
                    ">{{ ucfirst($invoice->status) }}</span>
            </div>

            {{-- Items --}}
            <div class="px-6 py-3">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-gray-500 text-xs uppercase">
                            <th class="text-left py-2">Fee Item</th>
                            <th class="text-right py-2">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-2 text-gray-700">{{ $item->feeType->name ?? $item->description }}</td>
                            <td class="py-2 text-right text-gray-800 font-medium">{{ setting('currency_symbol', 'UGX') }} {{ number_format($item->amount) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t">
                        <tr>
                            <td class="py-2 font-semibold text-gray-800">Total</td>
                            <td class="py-2 text-right font-bold text-gray-800">{{ setting('currency_symbol', 'UGX') }} {{ number_format($invoice->total_amount) }}</td>
                        </tr>
                        @if($invoice->paid_amount > 0)
                        <tr>
                            <td class="py-1 text-green-600">Paid</td>
                            <td class="py-1 text-right text-green-600">- {{ setting('currency_symbol', 'UGX') }} {{ number_format($invoice->paid_amount) }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 font-semibold {{ $invoice->balance > 0 ? 'text-red-600' : 'text-green-600' }}">Balance</td>
                            <td class="py-1 text-right font-bold {{ $invoice->balance > 0 ? 'text-red-600' : 'text-green-600' }}">{{ setting('currency_symbol', 'UGX') }} {{ number_format($invoice->balance) }}</td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>

            {{-- Payments --}}
            @if($invoice->payments->isNotEmpty())
            <div class="px-6 py-3 border-t bg-gray-50">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Payments</p>
                @foreach($invoice->payments as $payment)
                <div class="flex justify-between text-sm py-1">
                    <span class="text-gray-600">{{ $payment->receipt_number }} &mdash; {{ $payment->payment_date->format('d M Y') }}</span>
                    <span class="font-medium text-gray-800">{{ setting('currency_symbol', 'UGX') }} {{ number_format($payment->amount) }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border p-8 text-center">
            <p class="text-gray-400 text-sm">No invoices found</p>
        </div>
        @endforelse
    </div>
</x-app-layout>