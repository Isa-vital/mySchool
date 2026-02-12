<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $student->full_name }}</h2>
            <div class="flex items-center space-x-3">
                @can('students.edit')
                <a href="{{ route('students.edit', $student) }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Edit</a>
                @endcan
                <a href="{{ route('students.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Profile Card --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="text-center mb-6">
                <div class="w-24 h-24 rounded-full bg-gray-200 mx-auto flex items-center justify-center text-2xl font-bold text-gray-600 overflow-hidden">
                    @if($student->photo)
                        <img src="{{ Storage::url($student->photo) }}" class="w-full h-full object-cover" alt="">
                    @else
                        {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                    @endif
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-3">{{ $student->full_name }}</h3>
                <p class="text-sm text-gray-500">{{ $student->admission_number }}</p>
                @php
                    $statusColors = ['active' => 'green', 'graduated' => 'blue', 'transferred' => 'yellow', 'withdrawn' => 'red', 'suspended' => 'orange'];
                    $color = $statusColors[$student->status] ?? 'gray';
                @endphp
                <span class="mt-2 inline-block px-3 py-1 text-xs font-medium rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 capitalize">{{ $student->status }}</span>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Gender</span><span class="text-gray-900 capitalize">{{ $student->gender ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Date of Birth</span><span class="text-gray-900">{{ $student->date_of_birth?->format('d M Y') ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Nationality</span><span class="text-gray-900">{{ $student->nationality ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Religion</span><span class="text-gray-900">{{ $student->religion ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Blood Group</span><span class="text-gray-900">{{ $student->blood_group ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Phone</span><span class="text-gray-900">{{ $student->phone ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="text-gray-900">{{ $student->email ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Admission Date</span><span class="text-gray-900">{{ $student->admission_date?->format('d M Y') ?? '-' }}</span></div>
            </div>
        </div>

        {{-- Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Guardians --}}
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Guardians</h3>
                </div>
                <div class="divide-y">
                    @forelse($student->guardians as $guardian)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $guardian->first_name }} {{ $guardian->last_name }}</p>
                                    <p class="text-sm text-gray-500 capitalize">{{ $guardian->pivot->is_primary ? '★ Primary' : '' }} {{ $guardian->relationship ?? '' }}</p>
                                </div>
                                <div class="text-right text-sm">
                                    <p class="text-gray-600">{{ $guardian->phone ?? '' }}</p>
                                    <p class="text-gray-500">{{ $guardian->email ?? '' }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500">No guardians linked.</div>
                    @endforelse
                </div>
            </div>

            {{-- Enrollment History --}}
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Enrollment History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Academic Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Section</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($student->enrollments as $enrollment)
                                <tr>
                                    <td class="px-6 py-3 text-sm text-gray-900">{{ $enrollment->academicYear->name ?? '-' }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-900">{{ $enrollment->schoolClass->name ?? '-' }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-900">{{ $enrollment->section->name ?? '-' }}</td>
                                    <td class="px-6 py-3 text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full capitalize {{ $enrollment->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $enrollment->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">No enrollment records.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Address & Medical --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="font-semibold text-gray-800 mb-2">Address</h3>
                    <p class="text-sm text-gray-600">{{ $student->address ?? 'Not provided' }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="font-semibold text-gray-800 mb-2">Medical Conditions</h3>
                    <p class="text-sm text-gray-600">{{ $student->medical_conditions ?? 'None recorded' }}</p>
                </div>
            </div>

            {{-- Financial Summary --}}
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Fee & Invoices</h3>
                    <a href="{{ route('payments.create', ['student_id' => $student->id]) }}" class="text-sm font-medium" style="color: var(--primary-color);">+ Record Payment</a>
                </div>
                @if($student->invoices->count())
                    <div class="grid grid-cols-3 gap-4 p-6 border-b">
                        <div class="text-center">
                            <p class="text-xs text-gray-500 uppercase">Total Billed</p>
                            <p class="text-lg font-bold text-gray-900">UGX {{ number_format($student->invoices->sum('total_amount')) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-500 uppercase">Total Paid</p>
                            <p class="text-lg font-bold text-green-600">UGX {{ number_format($student->invoices->sum('amount_paid')) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-500 uppercase">Balance</p>
                            <p class="text-lg font-bold {{ $student->invoices->sum('balance') > 0 ? 'text-red-600' : 'text-green-600' }}">UGX {{ number_format($student->invoices->sum('balance')) }}</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($student->invoices as $invoice)
                                    <tr>
                                        <td class="px-6 py-3 text-sm font-mono text-gray-900">{{ $invoice->invoice_number }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-600">{{ $invoice->term->name ?? '-' }}</td>
                                        <td class="px-6 py-3 text-sm text-right text-gray-900">{{ number_format($invoice->total_amount) }}</td>
                                        <td class="px-6 py-3 text-sm text-right text-green-600">{{ number_format($invoice->amount_paid) }}</td>
                                        <td class="px-6 py-3 text-sm">
                                            @php $sc = ['unpaid' => 'red', 'partial' => 'yellow', 'paid' => 'green']; $c = $sc[$invoice->status] ?? 'gray'; @endphp
                                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $c }}-100 text-{{ $c }}-700 capitalize">{{ $invoice->status }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-sm text-right">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:underline">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-gray-500">No invoices yet.</div>
                @endif
            </div>

            {{-- Payment History --}}
            @if($student->payments->count())
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Payment History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($student->payments as $payment)
                                <tr>
                                    <td class="px-6 py-3 text-sm text-gray-900">{{ $payment->receipt_number }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $payment->payment_date?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $payment->invoice->invoice_number ?? '-' }}</td>
                                    <td class="px-6 py-3 text-sm text-right font-semibold text-green-600">UGX {{ number_format($payment->amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Library / Books Borrowed --}}
            @if(isset($bookIssues) && $bookIssues->count())
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Library Books</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issued</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Returned</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($bookIssues as $issue)
                                <tr>
                                    <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $issue->book->title ?? '-' }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $issue->issue_date?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-6 py-3 text-sm {{ $issue->status === 'issued' && $issue->due_date && $issue->due_date->isPast() ? 'text-red-600 font-semibold' : 'text-gray-600' }}">{{ $issue->due_date?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $issue->return_date?->format('d M Y') ?? '—' }}</td>
                                    <td class="px-6 py-3">
                                        @php $colors = ['issued' => 'bg-blue-100 text-blue-700', 'returned' => 'bg-green-100 text-green-700', 'overdue' => 'bg-red-100 text-red-700']; @endphp
                                        <span class="px-2 py-1 text-xs rounded-full {{ $colors[$issue->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($issue->status) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
