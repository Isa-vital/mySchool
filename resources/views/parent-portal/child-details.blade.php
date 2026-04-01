<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('parent.dashboard') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $student->full_name }}</h2>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Student Info --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="text-center mb-4">
                <div class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-3">
                    <span class="text-blue-600 font-bold text-2xl">{{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">{{ $student->full_name }}</h3>
                <p class="text-sm text-gray-500">{{ $student->admission_number }}</p>
            </div>

            <div class="divide-y text-sm">
                <div class="py-2 flex justify-between"><span class="text-gray-500">Gender</span><span class="font-medium">{{ ucfirst($student->gender ?? '-') }}</span></div>
                <div class="py-2 flex justify-between"><span class="text-gray-500">DOB</span><span class="font-medium">{{ $student->date_of_birth ? $student->date_of_birth->format('d M Y') : '-' }}</span></div>
                <div class="py-2 flex justify-between"><span class="text-gray-500">Blood Group</span><span class="font-medium">{{ $student->blood_group ?? '-' }}</span></div>
                @if($student->enrollments->isNotEmpty())
                @php $enrollment = $student->enrollments->first(); @endphp
                <div class="py-2 flex justify-between"><span class="text-gray-500">Class</span><span class="font-medium">{{ $enrollment->schoolClass->name ?? '-' }} {{ $enrollment->section->name ?? '' }}</span></div>
                <div class="py-2 flex justify-between"><span class="text-gray-500">Year</span><span class="font-medium">{{ $enrollment->academicYear->name ?? '-' }}</span></div>
                @endif
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('parent.attendance', $student) }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
                    <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-800 group-hover:underline">Attendance</h4>
                    <p class="text-sm text-gray-500 mt-1">View monthly attendance records</p>
                </a>

                <a href="{{ route('parent.results', $student) }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
                    <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-800 group-hover:underline">Exam Results</h4>
                    <p class="text-sm text-gray-500 mt-1">View published exam grades</p>
                </a>

                <a href="{{ route('parent.fees', $student) }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
                    <div class="w-12 h-12 rounded-lg bg-orange-50 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-800 group-hover:underline">Fees & Invoices</h4>
                    <p class="text-sm text-gray-500 mt-1">View invoices and payments</p>
                </a>
            </div>

            {{-- Recent Invoices --}}
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Invoices</h3>
                </div>
                <div class="divide-y">
                    @forelse($student->invoices->take(5) as $invoice)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $invoice->invoice_number }}</p>
                            <p class="text-xs text-gray-500">{{ $invoice->term->name ?? '' }} &mdash; {{ $invoice->academicYear->name ?? '' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-800">{{ setting('currency_symbol', 'UGX') }} {{ number_format($invoice->total_amount) }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : ($invoice->status === 'partial' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">No invoices yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>