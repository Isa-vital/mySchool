<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </x-slot>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Total Students --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Students</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_students']) }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-sm text-green-600 font-medium">{{ number_format($stats['total_enrolled']) }} enrolled</span>
            </div>
        </div>

        {{-- Total Staff --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Staff</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_staff']) }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>

        {{-- Attendance Today --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Attendance Today</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['attendance_today']) }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-sm text-red-600 font-medium">{{ number_format($stats['absent_today']) }} absent</span>
            </div>
        </div>

        {{-- Fees --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Fees Collected</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ setting('currency_symbol', 'UGX') }} {{ number_format($stats['total_fees_collected']) }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-sm text-orange-600 font-medium">{{ setting('currency_symbol', 'UGX') }} {{ number_format($stats['total_fees_pending']) }} pending</span>
            </div>
        </div>
    </div>

    {{-- Tables Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Students --}}
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Recent Students</h3>
                <a href="{{ route('students.index') }}" class="text-sm font-medium hover:underline" style="color: var(--primary-color);">View All</a>
            </div>
            <div class="divide-y">
                @forelse($stats['recent_students'] as $student)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-600">
                                {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $student->full_name }}</p>
                                <p class="text-xs text-gray-500">{{ $student->admission_number }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400">{{ $student->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500">No students yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Payments --}}
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Recent Payments</h3>
                <a href="{{ route('payments.index') }}" class="text-sm font-medium hover:underline" style="color: var(--primary-color);">View All</a>
            </div>
            <div class="divide-y">
                @forelse($stats['recent_payments'] as $payment)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $payment->student->full_name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $payment->receipt_number }} &bull; {{ $payment->payment_method }}</p>
                        </div>
                        <span class="text-sm font-semibold text-green-600">{{ setting('currency_symbol', 'UGX') }} {{ number_format($payment->amount) }}</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500">No payments yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
