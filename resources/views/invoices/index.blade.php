<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Invoices</h2>
            <div class="flex items-center space-x-3">
                @can('invoices.create')
                <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Invoice
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Student name or invoice number..." class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
            </div>
            <div class="w-40">
                <select name="status" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">All Status</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Filter</button>
            <a href="{{ route('invoices.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Clear</a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-mono text-gray-900">{{ $invoice->invoice_number }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $invoice->student->full_name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900">{{ setting('currency_symbol', 'UGX') }} {{ number_format($invoice->total_amount) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-green-600">{{ setting('currency_symbol', 'UGX') }} {{ number_format($invoice->amount_paid) }}</td>
                            <td class="px-6 py-4 text-sm text-right font-semibold {{ $invoice->balance > 0 ? 'text-red-600' : 'text-green-600' }}">{{ setting('currency_symbol', 'UGX') }} {{ number_format($invoice->balance) }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $sc = ['unpaid' => 'red', 'partial' => 'yellow', 'paid' => 'green', 'cancelled' => 'gray'];
                                    $color = $sc[$invoice->status] ?? 'gray';
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 capitalize">{{ $invoice->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-800 mr-2">View</a>
                                <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="text-green-600 hover:text-green-800">Pay</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="px-6 py-4 border-t">{{ $invoices->links() }}</div>
        @endif
    </div>
</x-app-layout>
