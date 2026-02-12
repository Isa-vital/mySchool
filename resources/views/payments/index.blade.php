<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Payments</h2>
            @can('payments.create')
            <a href="{{ route('payments.create') }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">+ Record Payment</a>
            @endcan
        </div>
    </x-slot>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search receipt# or student..." class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="rounded-lg border-gray-300 text-sm">
            <input type="date" name="to_date" value="{{ request('to_date') }}" class="rounded-lg border-gray-300 text-sm">
            <div class="flex space-x-2">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">Filter</button>
                <a href="{{ route('payments.index') }}" class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50">Clear</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $payment->receipt_number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $payment->student->full_name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $payment->payment_date?->format('d M Y') ?? $payment->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ $payment->payment_method }}</td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-green-600">{{ setting('currency_symbol', 'UGX') }} {{ number_format($payment->amount) }}</td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('payments.show', $payment) }}" class="text-sm font-medium" style="color: var(--primary-color);">View</a>
                            <a href="{{ route('payments.receipt', $payment) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">PDF</a>
                            @can('payments.delete')
                            <form method="POST" action="{{ route('payments.destroy', $payment) }}" class="inline" onsubmit="return confirm('Delete this payment?')">
                                @csrf @method('DELETE')
                                <button class="text-sm font-medium text-red-600 hover:text-red-800">Delete</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t">{{ $payments->links() }}</div>
    </div>
</x-app-layout>
